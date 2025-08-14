<?php
session_start();
include 'conexao.php';

$logado = isset($_SESSION['usuario_id']);
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
  http_response_code(404);
  echo "Produto não encontrado.";
  exit;
}

$stmt = $conn->prepare("SELECT id, nome, descricao, preco, categoria, imagem FROM produtos WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$prod = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$prod) {
  http_response_code(404);
  echo "Produto não encontrado.";
  exit;
}


$sug = [];
if (!empty($prod['categoria'])) {
  $stmt = $conn->prepare("SELECT id, nome, preco, imagem, categoria, descricao FROM produtos WHERE categoria = ? AND id <> ? ORDER BY RAND() LIMIT 10");
  $stmt->bind_param('si', $prod['categoria'], $id);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) $sug[] = $r;
  $stmt->close();
}
$conn->close();


$preco = (float)$prod['preco'];
$preco_br = number_format($preco, 2, ',', '.');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($prod['nome']) ?> | Bruiser Build</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0e0e0e;--panel:#181818;--card:#1f1f1f;--brand:#f0541e;--brand2:#ff3300;--text:#f4f4f4;--muted:#b3b3b3;
}
body{font-family:Inter,system-ui,Arial;background:linear-gradient(180deg,#0a0a0a,#121212);color:var(--text)}
a{color:inherit;text-decoration:none}


header{
  position:sticky;top:0;z-index:1000;background:#0f0f0fee;backdrop-filter:blur(6px);
  display:flex;align-items:center;justify-content:space-between;padding:10px 24px;border-bottom:1px solid #ffffff10;
}
.logo img{height:54px;border-radius:10px}
nav a{margin-left:16px;color:#fff}
nav a:hover{color:var(--brand)}


.container{max-width:1200px;margin:20px auto;padding:20px}


.product-grid{
  display:grid;grid-template-columns:1.1fr .9fr;gap:24px;align-items:start;
}
@media (max-width:980px){.product-grid{grid-template-columns:1fr}}


.gallery{background:var(--panel);border-radius:16px;padding:14px;position:relative;box-shadow:0 10px 30px rgba(0,0,0,.45)}
.main-img-wrap{
  width:100%;height:480px;background:#0a0a0a;border-radius:12px;overflow:hidden;position:relative;cursor:zoom-in;
}
.main-img-wrap img{
  width:100%;height:100%;object-fit:contain;transition:transform .25s ease;transform-origin:center center;
}
.thumbs{display:flex;gap:10px;margin-top:12px;overflow:auto;padding-bottom:4px}
.thumbs img{
  width:70px;height:70px;object-fit:contain;background:#0a0a0a;border:1px solid #ffffff12;border-radius:10px;cursor:pointer;transition:transform .2s, border .2s
}
.thumbs img:hover{transform:translateY(-2px);border-color:var(--brand)}

.modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.85);display:none;align-items:center;justify-content:center;z-index:2000}
.modal-bg.open{display:flex}
.modal-content{max-width:95vw;max-height:90vh}
.modal-content img{width:100%;height:100%;object-fit:contain}
.modal-close{
  position:absolute;top:20px;right:20px;background:#00000080;border:1px solid #ffffff20;color:#fff;
  padding:10px 12px;border-radius:10px;cursor:pointer
}


.details{background:var(--panel);border-radius:16px;padding:18px;box-shadow:0 10px 30px rgba(0,0,0,.45)}
.brandline{color:var(--brand);font-family:Orbitron,Inter,sans-serif;font-weight:700;letter-spacing:.6px;margin-bottom:6px}
.title{font-size:26px;font-weight:800;margin-bottom:8px}
.small{color:var(--muted);font-size:13px}
.price{font-size:30px;font-weight:900;margin:12px 0;color:#fff}
.badges{display:flex;flex-wrap:wrap;gap:8px;margin:10px 0}
.badge{background:#0c0c0c;border:1px solid #ffffff12;color:#eee;font-size:12px;padding:6px 10px;border-radius:999px}
.installments{background:#121212;border:1px solid #ffffff12;border-radius:12px;padding:12px;margin:12px 0}
.installments select{width:100%;padding:12px;border-radius:10px;border:1px solid #ffffff12;background:#0a0a0a;color:#fff}
.installments .row{display:flex;align-items:center;justify-content:space-between;margin-top:10px}
.pix{margin-top:8px;color:#8ef58e;font-weight:700}
.qty-row{display:flex;align-items:center;gap:10px;margin:14px 0}
.qty{display:flex;align-items:center;border:1px solid #ffffff12;border-radius:10px;overflow:hidden}
.qty button{background:#1b1b1b;border:none;color:#fff;width:36px;height:36px;cursor:pointer}
.qty input{width:56px;text-align:center;background:#0d0d0d;border:none;color:#fff;height:36px}
.actions{display:flex;gap:10px;flex-wrap:wrap}
.btn{
  padding:14px 16px;border:none;border-radius:12px;cursor:pointer;font-weight:800;
  background:linear-gradient(90deg,var(--brand),var(--brand2));color:#fff;box-shadow:0 8px 20px rgba(255,69,0,.25);
}
.btn.secondary{background:#222;color:#fff;border:1px solid #ffffff10;box-shadow:none}
.btn.icon{display:inline-flex;align-items:center;gap:8px}
.btn:active{transform:scale(.98)}


.description{background:var(--card);border-radius:16px;padding:16px;margin-top:20px;line-height:1.6}


.suggest-title{margin:28px 0 12px;font-size:20px;font-weight:800}
.suggest{
  display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px
}
.s-card{
  background:var(--card);border-radius:14px;padding:12px;transition:transform .2s, box-shadow .2s;opacity:0;transform:translateY(12px)
}
.s-card.reveal{animation:reveal .45s ease forwards}
.s-card:hover{transform:translateY(-4px);box-shadow:0 10px 24px rgba(0,0,0,.35)}
.s-card img{width:100%;height:160px;object-fit:contain;background:#0b0b0b;border-radius:12px;margin-bottom:8px}
.s-card .price{font-size:16px;color:var(--brand)}
.s-card .row{display:flex;gap:8px;margin-top:8px}
.s-card .row .btn{flex:1;padding:10px}


.toast{
  position:fixed;top:24px;left:50%;transform:translateX(-50%);z-index:3000;
  background:linear-gradient(90deg,var(--brand),var(--brand2));color:#fff;padding:12px 18px;border-radius:10px;
  display:none;font-weight:800;box-shadow:0 10px 26px rgba(0,0,0,.6)
}


.fade-in{animation:fadeIn .6s ease both}
@keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
@keyframes reveal{to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>
<header>
    <div class="logo">
        <a href="index.php">
            <img src="imagens/logo-bruiser.png" alt="Bruiser Build" style="height: 60px;">
        </a>
    </div>

    <nav>
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <a href="index.php">Início</a>
            <a href="perfil.php">Perfil</a>
            <a href="favoritos.php" title="Meus Favoritos ❤️">Favoritos</a>
            <a href="carrinho.php">Carrinho</a>
            <a href="logout.php">Sair</a>
        <?php else: ?>
            <a href="index.php">Início</a>
            <a href="cadastro.php">Cadastrar</a>
            <a href="login.php">Login</a>
            <a href="favoritos.php" title="Meus Favoritos ❤️">Favoritos</a>
            <a href="carrinho.php">Carrinho</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
  <div class="product-grid fade-in">

    <!-- GALERIA -->
    <section class="gallery">
      <div class="main-img-wrap" id="zoomWrap" title="Clique para ampliar">
        <img id="mainImg" src="<?= htmlspecialchars($prod['imagem']) ?>" alt="<?= htmlspecialchars($prod['nome']) ?>">
      </div>
      <div class="thumbs" id="thumbs">
        <!-- repetimos a imagem principal nas thumbs (pode adicionar mais se tiver) -->
        <img src="<?= htmlspecialchars($prod['imagem']) ?>" alt="thumb 1" onclick="trocarImagem(this.src)">
      </div>
    </section>

    <!-- DETALHES -->
    <section class="details">
      <div class="brandline"><?= htmlspecialchars($prod['categoria'] ?: 'Produto') ?></div>
      <h1 class="title"><?= htmlspecialchars($prod['nome']) ?></h1>
      <div class="small">Cód: #<?= (int)$prod['id'] ?> • Envio rápido</div>

      <div class="badges">
        <div class="badge"><i class="fa fa-shield"></i> Garantia 12 meses</div>
        <div class="badge"><i class="fa fa-truck-fast"></i> Frete rápido</div>
        <div class="badge"><i class="fa fa-rotate"></i> Devolução 7 dias</div>
      </div>

      <div class="price" id="price">R$ <?= $preco_br ?></div>

      <!-- PARCELAMENTO -->
      <div class="installments">
        <label for="parcelas">Parcelamento</label>
        <select id="parcelas" onchange="atualizarParcelas()">
          <?php for($i=1;$i<=12;$i++): ?>
            <option value="<?= $i ?>"><?= $i ?>x sem juros</option>
          <?php endfor; ?>
        </select>
        <div class="row">
          <div class="small">Mensal</div>
          <div id="valorParcela" style="font-weight:800">R$ 0,00</div>
        </div>
        <div class="pix"><i class="fa fa-qrcode"></i> PIX com <strong>10% OFF</strong>: <span id="pixPrice">R$ 0,00</span></div>
      </div>

      <!-- QUANTIDADE + AÇÕES -->
      <div class="qty-row">
        <div class="qty">
          <button type="button" onclick="addQty(-1)">−</button>
          <input id="qty" type="number" min="1" value="1" />
          <button type="button" onclick="addQty(1)">+</button>
        </div>

        <button class="btn icon" onclick="toggleFav()">
    <i id="favIcon" class="fa-regular fa-heart"></i> Favoritar
</button>

        <button class="btn secondary icon" onclick="compartilhar()">
          <i class="fa fa-share-nodes"></i> Compartilhar
        </button>
      </div>

      <div class="actions">
        <button class="btn icon" onclick="btnAdicionar()"><i class="fa fa-cart-plus"></i> Adicionar ao carrinho</button>
        <button class="btn secondary icon" onclick="btnComprarAgora()"><i class="fa fa-bolt"></i> Comprar agora</button>
      </div>

      <div class="description">
        <h3>Descrição</h3>
        <p style="margin-top:8px"><?= nl2br(htmlspecialchars($prod['descricao'])) ?></p>
      </div>
    </section>
  </div>

  <?php if (count($sug)): ?>
    <h3 class="suggest-title fade-in">Talvez você também curta</h3>
    <div class="suggest" id="sug">
      <?php foreach($sug as $s): ?>
        <div class="s-card">
          <a href="produto.php?id=<?= (int)$s['id'] ?>">
            <img src="<?= htmlspecialchars($s['imagem']) ?>" alt="<?= htmlspecialchars($s['nome']) ?>">
            <div style="font-weight:700;min-height:42px"><?= htmlspecialchars($s['nome']) ?></div>
          </a>
          <div class="price">R$ <?= number_format((float)$s['preco'],2,',','.') ?></div>
          <div class="row">
            <?php if ($logado): ?>
              <button class="btn" onclick='event.stopPropagation(); adicionarAoCarrinho({
                id: <?= (int)$s["id"] ?>,
                nome: "<?= addslashes($s["nome"]) ?>",
                preco: <?= (float)$s["preco"] ?>,
                imagem: "<?= addslashes($s["imagem"]) ?>",
                quantidade: 1
              })'><i class="fa fa-plus"></i> Carrinho</button>
            <?php else: ?>
              <button class="btn" style="background:#555;cursor:not-allowed" onclick="alert('⚠️ Faça login para adicionar ao carrinho');location.href='login.php'">
                <i class="fa fa-lock"></i> Carrinho
              </button>
            <?php endif; ?>
            <a class="btn secondary" href="produto.php?id=<?= (int)$s['id'] ?>">Ver</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>


<div class="modal-bg" id="imgModal" role="dialog" aria-modal="true">
  <button class="modal-close" onclick="fecharModal()"><i class="fa fa-xmark"></i> Fechar</button>
  <div class="modal-content">
    <img id="modalImg" src="<?= htmlspecialchars($prod['imagem']) ?>" alt="Zoom">
  </div>
</div>

<div class="toast" id="toast">Item adicionado ao carrinho</div>

<script>

const usuarioLogado = <?= $logado ? 'true' : 'false' ?>;
const produto = {
  id: <?= (int)$prod['id'] ?>,
  nome: "<?= addslashes($prod['nome']) ?>",
  preco: <?= $preco ?>,
  imagem: "<?= addslashes($prod['imagem']) ?>"
};


function toast(msg, err=false){
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.display = 'block';
  t.style.background = err ? 'linear-gradient(90deg,#ff3b3b,#ff6b6b)' : 'linear-gradient(90deg,var(--brand),var(--brand2))';
  clearTimeout(t._timer);
  t._timer = setTimeout(()=> t.style.display='none', 3000);
}


function addQty(n){
  const q = document.getElementById('qty');
  const v = Math.max(1, (parseInt(q.value||'1',10)+n));
  q.value = v;
}


function adicionarAoCarrinho(item, qtd = 1){
  if(!usuarioLogado){
    alert('⚠️ Você precisa estar logado para adicionar itens ao carrinho.');
    location.href = 'login.php'; return;
  }
  let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
  const i = carrinho.findIndex(p => p.id === item.id);
  if(i>=0){ carrinho[i].quantidade += qtd; }
  else { carrinho.push({...item, quantidade:qtd}); }
  localStorage.setItem('carrinho', JSON.stringify(carrinho));
  toast('Produto adicionado ao carrinho!');
}
function btnAdicionar(){
  const qtd = Math.max(1, parseInt(document.getElementById('qty').value||'1',10));
  adicionarAoCarrinho(produto, qtd);
}
function btnComprarAgora(){
  const qtd = Math.max(1, parseInt(document.getElementById('qty').value||'1',10));
  adicionarAoCarrinho(produto, qtd);
  location.href = 'carrinho.php';
}


function toggleFav(){
    if (!usuarioLogado) {
        alert("⚠️ Você precisa estar logado para favoritar itens.");
        window.location.href = "login.php";
        return;
    }

    let favoritos = JSON.parse(localStorage.getItem('favoritos')) || [];
    const index = favoritos.findIndex(p => p.id === produto.id);

    if(index >= 0){
        favoritos.splice(index, 1);
        document.getElementById('favIcon').className = 'fa-regular fa-heart';
        toast('Removido dos favoritos');
    } else {
        favoritos.push(produto);
        document.getElementById('favIcon').className = 'fa-solid fa-heart';
        toast('Adicionado aos favoritos');
    }

    localStorage.setItem('favoritos', JSON.stringify(favoritos));
}


(function initFav(){
    let favoritos = JSON.parse(localStorage.getItem('favoritos')) || [];
    if(favoritos.find(p => p.id === produto.id)){
        document.getElementById('favIcon').className = 'fa-solid fa-heart';
    }
})();

(function initFav(){
    let favoritos = JSON.parse(localStorage.getItem('favoritos')) || [];
    if(favoritos.find(p => p.id === produto.id)){
        document.getElementById('favIcon').className = 'fa-solid fa-heart';
    }
})();

function mostrarPopupFavorito(produtoNome) {
    const popup = document.createElement('div');
    popup.classList.add('popup');
    popup.innerHTML = `❤️ <strong>${produtoNome}</strong> adicionado aos favoritos!`;
    document.body.appendChild(popup);

    setTimeout(() => {
        popup.remove();
    }, 3500);
}

function compartilhar(){
  const url = location.href;
  const title = document.title;
  if(navigator.share){
    navigator.share({title, text: produto.nome, url}).catch(()=>{});
  }else{
    navigator.clipboard.writeText(url).then(()=> toast('Link copiado!'));
  }
}


function atualizarParcelas(){
  const sel = document.getElementById('parcelas');
  const n = parseInt(sel.value,10);
  const mensal = produto.preco / n;
  document.getElementById('valorParcela').textContent = 'R$ ' + mensal.toFixed(2).replace('.',',');
  const pix = produto.preco * 0.90; 
  document.getElementById('pixPrice').textContent = 'R$ ' + pix.toFixed(2).replace('.',',');
}
atualizarParcelas();


const wrap = document.getElementById('zoomWrap');
const img = document.getElementById('mainImg');
wrap.addEventListener('mousemove', (e)=>{
  const r = wrap.getBoundingClientRect();
  const x = ((e.clientX - r.left) / r.width)*100;
  const y = ((e.clientY - r.top) / r.height)*100;
  img.style.transformOrigin = `${x}% ${y}%`;
  img.style.transform = 'scale(1.5)';
});
wrap.addEventListener('mouseleave', ()=>{
  img.style.transform = 'scale(1)';
  img.style.transformOrigin = 'center';
});
wrap.addEventListener('click', ()=>{
  document.getElementById('imgModal').classList.add('open');
  document.getElementById('modalImg').src = img.src;
});
function fecharModal(){ document.getElementById('imgModal').classList.remove('open'); }
function trocarImagem(src){ img.src = src; }


const obs = new IntersectionObserver((entries)=>{
  entries.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('reveal'); obs.unobserve(e.target); }});
},{threshold:.15});
document.querySelectorAll('.s-card').forEach(c=>obs.observe(c));
</script>
</body>
</html>
