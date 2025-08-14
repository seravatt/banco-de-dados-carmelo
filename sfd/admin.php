<?php
session_start();
include 'conexao.php';


if (!isset($_SESSION['usuario_id'])) {
  
    if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_GET['fetch']) || isset($_GET['get'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'msg' => 'Sessão inválida. Faça login novamente.']);
        exit;
    } else {
        header('Location: login.php');
        exit;
    }
}

$usuario_id = intval($_SESSION['usuario_id']);
$stmt = $conn->prepare("SELECT nivel FROM usuarios WHERE id = ?");
if (!$stmt) {
    die("Erro DB: " . $conn->error);
}
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user || ($user['nivel'] ?? '') !== 'admin') {
    
    if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_GET['fetch']) || isset($_GET['get'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'msg' => 'Acesso negado.']);
        exit;
    } else {
        header('Location: index.php');
        exit;
    }
}


if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}
$csrf_token = $_SESSION['csrf_token'];


if (isset($_GET['fetch']) && $_GET['fetch'] == '1') {
    header('Content-Type: application/json; charset=utf-8');
    $arr = [];
    $res = $conn->query("SELECT id, nome, descricao, preco, categoria, imagem FROM produtos ORDER BY id DESC");
    if ($res === false) {
        echo json_encode(['success' => false, 'msg' => 'Erro DB: ' . $conn->error]);
        exit;
    }
    while ($r = $res->fetch_assoc()) {
      
        $r['imagem'] = $r['imagem'] ? $r['imagem'] : null;
        $arr[] = $r;
    }
    echo json_encode(['success' => true, 'produtos' => $arr]);
    exit;
}

if (isset($_GET['get']) && intval($_GET['get']) > 0) {
    $id = intval($_GET['get']);
    $stmt = $conn->prepare("SELECT id, nome, descricao, preco, categoria, imagem FROM produtos WHERE id = ?");
    if (!$stmt) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'msg' => 'Erro DB prepare: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    header('Content-Type: application/json; charset=utf-8');
    if ($res->num_rows === 0) {
        echo json_encode(['success' => false, 'msg' => 'Produto não encontrado']);
        exit;
    }
    $prod = $res->fetch_assoc();
    echo json_encode(['success' => true, 'produto' => $prod]);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['action']) || isset($_REQUEST['action']))) {
    header('Content-Type: application/json; charset=utf-8');

 
    $postedToken = $_POST['csrf_token'] ?? $_REQUEST['csrf_token'] ?? '';
    if (empty($postedToken) || !hash_equals($csrf_token, $postedToken)) {
        echo json_encode(['success' => false, 'msg' => 'CSRF inválido. Tente novamente.']);
        exit;
    }

    $action = $_POST['action'] ?? $_REQUEST['action'];


    function resposta_json($success, $msg = '', $extra = []) {
        echo json_encode(array_merge(['success' => $success, 'msg' => $msg], $extra));
        exit;
    }

    function input($key) {
        if (isset($_POST[$key])) return trim($_POST[$key]);
        if (isset($_REQUEST[$key])) return trim($_REQUEST[$key]);
        return '';
    }


    function handleImageUpload($fileInputName, $existingFilename = null) {
        $UPLOAD_DIR = __DIR__ . '/uploads/';
        if (!is_dir($UPLOAD_DIR)) {
            mkdir($UPLOAD_DIR, 0755, true);
        }

        if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] === UPLOAD_ERR_NO_FILE) {
            return $existingFilename; 
        }

        $f = $_FILES[$fileInputName];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Erro no upload (código ' . $f['error'] . ').'];
        }

       
        if ($f['size'] > 6 * 1024 * 1024) {
            return ['error' => 'Arquivo muito grande (máx 6MB).'];
        }

  
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $f['tmp_name']);
        finfo_close($finfo);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) {
            return ['error' => 'Formato inválido. Use JPG, PNG ou WEBP.'];
        }
        $ext = $allowed[$mime];


        $safeName = bin2hex(random_bytes(10)) . '.' . $ext;
        $dest = $UPLOAD_DIR . $safeName;
        if (!move_uploaded_file($f['tmp_name'], $dest)) {
            return ['error' => 'Falha ao mover arquivo.'];
        }


        if ($existingFilename) {
            $oldRel = $existingFilename;
            $old = __DIR__ . '/' . ltrim($oldRel, '/');
            if (is_file($old) && strpos(realpath($old), realpath($UPLOAD_DIR)) === 0) {
                @unlink($old);
            }
        }

        return 'uploads/' . $safeName;
    }


    if ($action === 'add') {
        $nome = input('nome');
        $descricao = input('descricao');
        $preco = input('preco');
        $categoria = input('categoria');
        $imagem_url = input('imagem_url');

        if ($nome === '' || $preco === '') {
            resposta_json(false, 'Nome e preço são obrigatórios.');
        }

 
        $preco = str_replace(',', '.', $preco);
        if (!is_numeric($preco)) resposta_json(false, 'Preço inválido.');
        $preco = number_format((float)$preco, 2, '.', '');

   
        $imgResult = handleImageUpload('imagem');
        if (is_array($imgResult) && isset($imgResult['error'])) {
            resposta_json(false, $imgResult['error']);
        }
        $imagemPath = $imgResult;

        if (!$imagemPath && $imagem_url) {
 
            if (!filter_var($imagem_url, FILTER_VALIDATE_URL)) {
                resposta_json(false, 'URL de imagem inválida.');
            }
            $imagemPath = $imagem_url;
        }


        $stmt = $conn->prepare("INSERT INTO produtos (nome, descricao, preco, categoria, imagem) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) resposta_json(false, 'Erro DB prepare: ' . $conn->error);
        $stmt->bind_param('ssdss', $nome, $descricao, $preco, $categoria, $imagemPath);
        if ($stmt->execute()) {
            $insertedId = $stmt->insert_id;
            resposta_json(true, 'Produto adicionado com sucesso.', ['id' => $insertedId]);
        } else {
            resposta_json(false, 'Erro ao adicionar produto: ' . $stmt->error);
        }
    }


    if ($action === 'edit') {
        $id = intval(input('id'));
        $nome = input('nome');
        $descricao = input('descricao');
        $preco = input('preco');
        $categoria = input('categoria');
        $imagem_url = input('imagem_url');

        if ($id <= 0) resposta_json(false, 'ID inválido.');
        if ($nome === '' || $preco === '') resposta_json(false, 'Nome e preço são obrigatórios.');

        $preco = str_replace(',', '.', $preco);
        if (!is_numeric($preco)) resposta_json(false, 'Preço inválido.');
        $preco = number_format((float)$preco, 2, '.', '');

     
        $stmt = $conn->prepare("SELECT imagem FROM produtos WHERE id = ?");
        if (!$stmt) resposta_json(false, 'Erro DB prepare: ' . $conn->error);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) resposta_json(false, 'Produto não encontrado.');
        $row = $res->fetch_assoc();
        $oldImagem = $row['imagem'];
        $stmt->close();

   
        $imgResult = handleImageUpload('imagem', $oldImagem);
        if (is_array($imgResult) && isset($imgResult['error'])) {
            resposta_json(false, $imgResult['error']);
        }
        $imagemPath = $imgResult;

     
        if ((!$imagemPath || $imagemPath === $oldImagem) && $imagem_url) {
            if (!filter_var($imagem_url, FILTER_VALIDATE_URL)) {
                resposta_json(false, 'URL de imagem inválida.');
            }
            $imagemPath = $imagem_url;
         
            if ($oldImagem && strpos($oldImagem, 'uploads/') === 0) {
                $oldFull = __DIR__ . '/' . ltrim($oldImagem, '/');
                if (is_file($oldFull)) @unlink($oldFull);
            }
        }

        $stmt = $conn->prepare("UPDATE produtos SET nome = ?, descricao = ?, preco = ?, categoria = ?, imagem = ? WHERE id = ?");
        if (!$stmt) resposta_json(false, 'Erro DB prepare: ' . $conn->error);
        $stmt->bind_param('ssdssi', $nome, $descricao, $preco, $categoria, $imagemPath, $id);
        if ($stmt->execute()) {
            resposta_json(true, 'Produto atualizado com sucesso.');
        } else {
            resposta_json(false, 'Erro ao atualizar: ' . $stmt->error);
        }
    }


    if ($action === 'delete') {
        $id = intval(input('id'));
        if ($id <= 0) resposta_json(false, 'ID inválido.');

    
        $stmt = $conn->prepare("SELECT imagem FROM produtos WHERE id = ?");
        if (!$stmt) resposta_json(false, 'Erro DB prepare: ' . $conn->error);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) resposta_json(false, 'Produto não encontrado.');
        $row = $res->fetch_assoc();
        $imagem = $row['imagem'];
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
        if (!$stmt) resposta_json(false, 'Erro DB prepare: ' . $conn->error);
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
           
            if ($imagem && strpos($imagem, 'uploads/') === 0) {
                $path = __DIR__ . '/' . ltrim($imagem, '/');
                if (is_file($path)) @unlink($path);
            }
            resposta_json(true, 'Produto excluído com sucesso.');
        } else {
            resposta_json(false, 'Erro ao excluir: ' . $stmt->error);
        }
    }

 
    resposta_json(false, 'Ação desconhecida.');
}


?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Painel Admin — Bruiser Build</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .fade-in-up {
  animation: fadeInUp 0.8s ease forwards;
  opacity: 0;
  transform: translateY(20px);
}

@keyframes fadeInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}


.scale-fade {
  animation: scaleFade 0.6s ease forwards;
  opacity: 0;
  transform: scale(0.95);
}

@keyframes scaleFade {
  to {
    opacity: 1;
    transform: scale(1);
  }
}
    :root{
      --bg:#0f0f0f;
      --card:#1b1b1b;
      --muted:#a0a0a0;
      --accent:#ff4500;
      --accent-2:#f0541e;
      --success:#00c853;
    }
    *{box-sizing:border-box}
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
      background:linear-gradient(180deg,#070707 0%, #0f0f0f 100%);
      color:#fff;
      -webkit-font-smoothing:antialiased;
    }
    .container{max-width:1200px;margin:100px auto;padding:20px;}
    header.admin-top{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:24px}
    .brand{display:flex;align-items:center;gap:12px}
    .brand img{height:56px;border-radius:8px}
    .brand h1{font-size:20px;margin:0;color:var(--accent)}
    .admin-actions{display:flex;gap:8px;align-items:center}
    .btn{background:var(--accent);color:#fff;padding:10px 12px;border:none;border-radius:8px;cursor:pointer;font-weight:600}
    .btn.ghost{background:transparent;border:1px solid rgba(255,255,255,0.06)}
    .stats{display:flex;gap:12px}
    .card{background:var(--card);padding:16px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,0.6)}
  
    .panel{display:grid;grid-template-columns: 1fr 380px; gap:18px; align-items:start}
   
    .list-head{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:linear-gradient(90deg,#0e0e0e,#171717);border-radius:10px;margin-bottom:10px}
    .table{width:100%;border-collapse:collapse}
    .table th,.table td{padding:12px 10px;text-align:left;border-bottom:1px solid rgba(255,255,255,0.03);font-size:14px}
    .table th{color:var(--muted);font-weight:600;font-size:13px}
    .product-thumb{width:64px;height:44px;object-fit:cover;border-radius:6px;border:1px solid rgba(255,255,255,0.03)}
    .actions td .icon{margin-right:8px;color:var(--muted);cursor:pointer}
    .actions td .icon.edit{color:var(--accent)}
    .actions td .icon.delete{color:#ff3b3b}
  
    .panel-right{position:sticky;top:100px}
    .form-row{display:flex;flex-direction:column;gap:8px;margin-bottom:12px}
    .form-row label{font-size:13px;color:var(--muted)}
    input[type=text], textarea, select{width:100%;padding:12px;border-radius:8px;border:1px solid rgba(255,255,255,0.04);background:#0b0b0b;color:#fff}
    textarea{min-height:100px;resize:vertical}
    .preview{display:block;width:100%;height:160px;background:linear-gradient(90deg,#0b0b0b,#141414);border-radius:8px;object-fit:contain;margin-bottom:10px;padding:10px}
    .small{font-size:13px;color:var(--muted)}

    .modal-bg{position:fixed;inset:0;background:rgba(0,0,0,0.6);display:none;align-items:center;justify-content:center;z-index:2000}
    .modal{background:var(--card);padding:20px;border-radius:12px;width:720px;max-width:95%}
    .modal h3{margin:0 0 12px;color:var(--accent)}
    .confirm-yes{background:#ff3b3b;padding:10px;border:none;border-radius:8px;color:#fff;cursor:pointer}
    .confirm-no{background:transparent;border:1px solid rgba(255,255,255,0.06);padding:10px;border-radius:8px;color:#fff;cursor:pointer}
  
    @media(max-width:980px){.panel{grid-template-columns:1fr} .panel-right{position:static;margin-top:20px}}

    .toast{position:fixed;left:50%;transform:translateX(-50%);top:24px;background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff;padding:16px 22px;border-radius:10px;z-index:3000;display:none;box-shadow:0 8px 24px rgba(0,0,0,0.6);font-weight:700}
    .toast.error{background:linear-gradient(90deg,#ff3b3b,#ff6b6b)}
    .searchbox{display:flex;gap:8px;align-items:center}
    .searchbox input{padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,0.04);background:#0b0b0b;color:#fff}
    .searchbox .clear{background:transparent;border:none;color:var(--muted);cursor:pointer}
    .hint{font-size:12px;color:var(--muted);margin-top:6px}
  </style>
</head>
<body class="fade-in-up">
  <div class="container scale-fade">
  <div class="container">
    <header class="admin-top">
      <div class="brand">
        <img src="imagens/logo-bruiser.png" alt="Bruiser Build">
        <div>
          <h1>Bruiser Build - Painel Administrativo</h1>
          <div class="small">Bem vindo(a), administrador</div>
        </div>
      </div>

      <div class="admin-actions">
        <div class="searchbox card" style="padding:8px 12px">
          <input id="searchInput" placeholder="Pesquisar por nome ou categoria...">
          <button class="clear" onclick="document.getElementById('searchInput').value=''; applyFilters();">✖</button>
        </div>
        <button class="btn" id="btnNovo">+ Novo Produto</button>
        <a class="btn ghost" href="index.php" target="_blank">Ver loja</a>
      </div>
    </header>

    <div class="panel">
      <div>
        <div class="list-head">
          <div>
            <strong>Produtos</strong>
            <div class="small">Gerencie catálogo de produtos — adicione, edite ou remova.</div>
          </div>
          <div class="small">Total: <span id="totalCount">0</span></div>
        </div>

        <div class="card">
          <table class="table" id="prodTable">
            <thead>
              <tr>
                <th>Produto</th>
                <th>Preço</th>
                <th>Categoria</th>
                <th style="width:110px;text-align:right">Ações</th>
              </tr>
            </thead>
            <tbody id="prodTbody">
       
            </tbody>
          </table>
        </div>
      </div>

      <aside class="panel-right card">
        <h3>Editar / Adicionar produto</h3>
        <form id="formProduto" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
          <input type="hidden" name="action" id="formAction" value="add">
          <input type="hidden" name="id" id="produtoId" value="0">

          <div class="form-row">
            <label>Nome</label>
            <input type="text" name="nome" id="nomeField" required>
          </div>

          <div class="form-row">
            <label>Descrição</label>
            <textarea name="descricao" id="descricaoField"></textarea>
          </div>

          <div class="form-row">
            <label>Preço (ex: 199.90)</label>
            <input type="text" name="preco" id="precoField" required>
          </div>

          <div class="form-row">
            <label>Categoria</label>
            <input type="text" name="categoria" id="categoriaField">
          </div>

          <div class="form-row">
            <label>Imagem (upload ou URL)</label>
            <img src="imagens/placeholder.png" class="preview" id="imgPreview" alt="preview">
            <input type="file" name="imagem" id="imagemField" accept="image/*">
            <div class="hint">Ou cole uma URL de imagem abaixo (o upload tem prioridade):</div>
            <input type="text" name="imagem_url" id="imagemUrlField" placeholder="https://...">
            <div class="small">Formats: JPG, PNG, WEBP. Máx 6MB</div>
          </div>

          <div style="display:flex;gap:8px">
            <button type="submit" class="btn" id="saveBtn">Salvar</button>
            <button type="button" class="btn ghost" id="resetBtn">Limpar</button>
          </div>
        </form>
      </aside>
    </div>
  </div>

 
  <div class="modal-bg" id="confirmModal">
    <div class="modal">
      <h3>Confirmar exclusão</h3>
      <p id="confirmText">Deseja realmente excluir este produto?</p>
      <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:14px">
        <button class="confirm-no" onclick="closeModal()">Cancelar</button>
        <button class="confirm-yes" id="confirmYes">Excluir</button>
      </div>
    </div>
  </div>

  <div class="toast" id="toast">Operação realizada</div>

<script>
const csrfToken = "<?php echo $csrf_token; ?>";
const apiUrl = location.pathname; 


function showToast(msg, duration = 3000, isError = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.toggle('error', isError);
  t.style.display = 'block';
  clearTimeout(t._hideTimer);
  t._hideTimer = setTimeout(()=> t.style.display = 'none', duration);
}


function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/[&<>"'`=\/]/g, s => ( {
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'
  } )[s]);
}
function escapeForAttr(str) {
  return (str || '').replace(/'/g, "\\'");
}


function renderTable(produtos) {
  const tbody = document.getElementById('prodTbody');
  tbody.innerHTML = '';
  produtos.forEach(p => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <div style="display:flex;gap:10px;align-items:center">
          <img src="${p.imagem ? p.imagem : 'imagens/placeholder.png'}" class="product-thumb" alt="">
          <div>
            <div style="font-weight:600">${escapeHtml(p.nome)}</div>
            <div class="small">${escapeHtml((p.descricao||'').substring(0,120))}</div>
          </div>
        </div>
      </td>
      <td>R$ ${parseFloat(p.preco || 0).toFixed(2)}</td>
      <td>${escapeHtml(p.categoria || '')}</td>
      <td class="actions" style="text-align:right">
        <span class="icon edit" title="Editar" onclick="openEdit(${p.id})"><i class="fa fa-pen"></i></span>
        <span class="icon delete" title="Excluir" onclick="openDelete(${p.id}, '${escapeForAttr(p.nome)}')"><i class="fa fa-trash"></i></span>
      </td>
    `;
    tbody.appendChild(tr);
  });
  document.getElementById('totalCount').innerText = produtos.length;
  applyFilters(); 
}


function fetchProdutos() {
  fetch(apiUrl + '?fetch=1', { credentials: 'same-origin' })
    .then(r => {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(data => {
      if (!data.success) {
        showToast('Erro ao carregar produtos: ' + (data.msg || 'sem mensagem'), 4000, true);
        return;
      }
      renderTable(data.produtos);
    })
    .catch(e => {
      console.error('fetchProdutos error:', e);
      showToast('Erro de rede ao buscar produtos.', 4000, true);
    });
}


document.addEventListener('DOMContentLoaded', ()=> {
  fetchProdutos();
});

document.getElementById('searchInput').addEventListener('input', applyFilters);
function applyFilters(){
  const q = document.getElementById('searchInput').value.trim().toLowerCase();
  const rows = Array.from(document.querySelectorAll('#prodTbody tr'));
  if (!q) {
    rows.forEach(r=> r.style.display='');
    return;
  }
  rows.forEach(r=>{
    const txt = r.innerText.toLowerCase();
    r.style.display = txt.includes(q) ? '' : 'none';
  });
}


let deleteId = 0;
function openDelete(id, name) {
  deleteId = id;
  document.getElementById('confirmText').innerText = `Deseja excluir o produto: ${name}? Esta ação é irreversível.`;
  document.getElementById('confirmModal').style.display = 'flex';
}
function closeModal() {
  document.getElementById('confirmModal').style.display = 'none';
}
document.getElementById('confirmYes').addEventListener('click', ()=> {
  fetch(apiUrl, {
    method: 'POST',
    body: new URLSearchParams({action:'delete', id: deleteId, csrf_token: csrfToken}),
    headers: {'X-Requested-With':'XMLHttpRequest'}
  }).then(r=>r.json()).then(json=>{
    if (json.success) {
      showToast(json.msg || 'Excluído', 3000, false);
      fetchProdutos();
    } else {
      showToast('Erro: ' + json.msg, 4000, true);
    }
    closeModal();
  }).catch(e=>{
    console.error(e); showToast('Erro ao excluir', 4000, true); closeModal();
  });
});


function openEdit(id) {
  fetch(apiUrl + '?get=' + id, { credentials: 'same-origin' })
    .then(r => {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(json => {
      if (!json.success) { showToast('Erro: ' + json.msg, 4000, true); return; }
      const p = json.produto;
      document.getElementById('formAction').value = 'edit';
      document.getElementById('produtoId').value = p.id;
      document.getElementById('nomeField').value = p.nome;
      document.getElementById('descricaoField').value = p.descricao;
      document.getElementById('precoField').value = parseFloat(p.preco).toFixed(2);
      document.getElementById('categoriaField').value = p.categoria;
      document.getElementById('imgPreview').src = p.imagem ? p.imagem : 'imagens/placeholder.png';
      document.getElementById('imagemUrlField').value = p.imagem && p.imagem.indexOf('http') === 0 ? p.imagem : '';
      window.scrollTo({top: 0, behavior: 'smooth'});
    }).catch(e=>{console.error(e); showToast('Erro ao carregar produto', 4000, true);});
}


document.getElementById('resetBtn').addEventListener('click', resetForm);
function resetForm(){
  document.getElementById('formAction').value = 'add';
  document.getElementById('produtoId').value = 0;
  document.getElementById('nomeField').value = '';
  document.getElementById('descricaoField').value = '';
  document.getElementById('precoField').value = '';
  document.getElementById('categoriaField').value = '';
  document.getElementById('imgPreview').src = 'imagens/placeholder.png';
  document.getElementById('imagemField').value = '';
  document.getElementById('imagemUrlField').value = '';
}


document.getElementById('imagemField').addEventListener('change', function(e){
  const f = this.files[0];
  if (!f) return;
  if (!f.type.startsWith('image/')) { showToast('Escolha uma imagem válida.', 3500, true); return; }
  const reader = new FileReader();
  reader.onload = () => { document.getElementById('imgPreview').src = reader.result; };
  reader.readAsDataURL(f);
});


document.getElementById('imagemUrlField').addEventListener('input', function(){
  const v = this.value.trim();
  if (!v) {return; }
 
  try {
    const u = new URL(v);
    
    document.getElementById('imgPreview').src = v;
  } catch(e) {
    
  }
});


document.getElementById('formProduto').addEventListener('submit', function(e){
  e.preventDefault();
  const form = e.target;
  const action = document.getElementById('formAction').value;
  const nome = form.nome.value.trim();
  const preco = form.preco.value.trim();
  if (!nome || !preco) { showToast('Preencha nome e preço.', 3000, true); return; }

  const formData = new FormData(form);
  
  fetch(apiUrl, {
    method: 'POST',
    body: formData,
    credentials: 'same-origin',
    headers: {'X-Requested-With':'XMLHttpRequest'}
  }).then(r=>{
    if (!r.ok) throw new Error('HTTP ' + r.status);
    return r.json();
  }).then(json=>{
    if (json.success) {
      showToast(json.msg || 'Operação realizada', 3000, false);
      resetForm();
      fetchProdutos();
    } else {
      showToast('Erro: ' + json.msg, 4500, true);
    }
  }).catch(e=>{
    console.error(e);
    showToast('Erro de rede', 4000, true);
  });
});


document.getElementById('btnNovo').addEventListener('click', ()=>{
  resetForm();
  window.scrollTo({top: 0, behavior: 'smooth'});
});
document.addEventListener("DOMContentLoaded", () => {
  document.body.style.opacity = "1";
  const sections = document.querySelectorAll(".card, .panel, header.admin-top");
  sections.forEach((el, i) => {
    el.style.animationDelay = `${i * 0.15}s`;
    el.classList.add("fade-in-up");
  });
});
</script>

</body>
</html>
