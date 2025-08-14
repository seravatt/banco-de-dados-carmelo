<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    $host = 'localhost';
    $dbname = 'sfd';
    $username = 'root';
    $password = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT nome, email, telefone, endereco FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            session_destroy();
            header('Location: login.php');
            exit;
        }
    } catch (PDOException $e) {
        die("Erro ao conectar ao banco: " . $e->getMessage());
    }
}

$host = 'localhost';
$dbname = 'sfd';
$username = 'root';
$password = '';
$mensagem = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $nome     = trim($_POST['nome']);
  $email    = trim($_POST['email']);
  $telefone = trim($_POST['telefone']);
  $endereco = trim($_POST['endereco']);
  $senha    = password_hash($_POST['senha'], PASSWORD_DEFAULT);

  $sql = "INSERT INTO usuarios (nome, email, telefone, endereco, nivel) VALUES (?, ?, ?, ?, 'usuario')";
  try {
      $stmt = $pdo->prepare($sql);
      $stmt->execute([$nome, $email, $telefone, $endereco]);

      $usuario_id = $pdo->lastInsertId();

      $sqlLogin = "INSERT INTO login (usuario_id, senha) VALUES (?, ?)";
      $stmtLogin = $pdo->prepare($sqlLogin);
      $stmtLogin->execute([$usuario_id, $senha]);

      header("Location: login.php?cadastro=sucesso");
      exit;

  } catch (PDOException $e) {
      $mensagem = '<p class="mensagem erro">❌ Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
  }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Cadastro - Bruiser Build</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap');

    *{box-sizing:border-box} body{margin:0}
    body {
        background-color:#121212;color:#ddd;display:flex;justify-content:center;align-items:center;
        min-height:100vh;padding:20px;font-family:'Montserrat',sans-serif;
    }
    header {
        position:fixed;top:0;left:0;right:0;height:70px;background:#121212;display:flex;justify-content:center;align-items:center;z-index:1000;
    }
    header .logo img {height:60px;cursor:pointer;border-radius:10px}
    main {
        margin-top:100px;width:100%;max-width:460px;background:#1f1f1f;border-radius:20px;padding:40px 35px;
        box-shadow:0 10px 30px rgba(0,0,0,.35);
        animation: slideFadeIn 0.8s ease forwards;
    }
    h2 {text-align:center;font-size:2rem;color:#e73e00;margin:0 0 25px}
    .mensagem{padding:12px;border-radius:10px;text-align:center;margin-bottom:18px;font-weight:700}
    .sucesso{background:#1a5e1a;color:#d4ffd4}.erro{background:#6a1a1a;color:#ffd4d4}

    .input-group{position:relative;margin-bottom:20px;opacity:0;animation:fadeUp 0.6s ease forwards}
    .input-group:nth-child(1) { animation-delay: 0.2s; }
    .input-group:nth-child(2) { animation-delay: 0.4s; }
    .input-group:nth-child(3) { animation-delay: 0.6s; }
    .input-group:nth-child(4) { animation-delay: 0.8s; }
    .input-group:nth-child(5) { animation-delay: 1s; }

    .input-group input,
    .input-group textarea{
        width:100%;padding:14px 46px 14px 46px;border-radius:12px;border:2px solid #444;background:#222;color:#fff;
        font-size:1rem;outline:none;transition:all .25s ease;
    }
    .input-group textarea{min-height:86px;resize:vertical}
    .input-group input:focus,.input-group textarea:focus{border-color:#e73e00;background:#2a2a2a}

    .input-group label{
        position:absolute;left:46px;top:50%;transform:translateY(-50%);font-size:.92rem;color:#aaa;
        transition:.25s ease;background:transparent;padding:0 6px;pointer-events:none;
    }
    .input-group input:focus + label,
    .input-group input:not(:placeholder-shown) + label,
    .input-group textarea:focus + label,
    .input-group textarea:not(:placeholder-shown) + label{
        top:-9px;left:12px;font-size:.75rem;color:#e73e00;background:#1f1f1f;border-radius:6px;
    }

    .icon-left{
        position:absolute;left:14px;top:50%;transform:translateY(-50%);
        width:20px;height:20px;fill:#666;pointer-events:none;transition:fill .25s ease;
    }
    .input-group input:focus ~ .icon-left,
    .input-group textarea:focus ~ .icon-left{fill:#e73e00}

    .toggle-password{
        position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;width:22px;height:22px;fill:#666;transition:transform .25s, fill .25s;
    }
    .toggle-password:hover{fill:#e73e00;transform:translateY(-50%) rotate(12deg)}

    button{
        width:100%;padding:15px;border:none;border-radius:12px;background:#e73e00;color:#fff;
        font-size:1.1rem;font-weight:800;cursor:pointer;transition:background .25s ease, transform .06s;
        opacity:0;animation:fadeUp 0.6s ease forwards;animation-delay:1.2s;
    }
    button:hover{background:#ff4d00} button:active{transform:scale(.99)}

 
    @keyframes slideFadeIn {
        0% {opacity: 0; transform: translateY(30px);}
        100% {opacity: 1; transform: translateY(0);}
    }
    @keyframes fadeUp {
        0% {opacity: 0; transform: translateY(20px);}
        100% {opacity: 1; transform: translateY(0);}
    }
</style>
</head>
<body>
<header>
  <div class="logo">
    <a href="index.php" title="Bruiser Build — Início"><img src="imagens/logo-bruiser.png" alt="Bruiser Build" /></a>
  </div>
</header>

<main role="main" aria-label="Formulário de cadastro">
  <h2>Cadastre-se</h2>
  <?= $mensagem ?>

  <form method="POST" autocomplete="off" novalidate>
    <div class="input-group">
      <input type="text" name="nome" id="nome" placeholder=" " required pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s]+" />
      <label for="nome">Nome Completo</label>
      <svg class="icon-left" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4S14.21 4 12 4 8 5.79 8 8s1.79 4 4 4zm0 2c-3.33 0-10 1.67-10 5v1h20v-1c0-3.33-6.67-5-10-5z"/></svg>
    </div>

    <div class="input-group">
      <input type="email" name="email" id="email" placeholder=" " required maxlength="100" />
      <label for="email">Email</label>
      <svg class="icon-left" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16a2 2 0 0 0 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5L4 8V6l8 5 8-5v2z"/></svg>
    </div>

    <div class="input-group">
      <input type="tel" name="telefone" id="telefone" placeholder=" " required pattern="\d{10,11}" title="Somente números (10 a 11 dígitos)" />
      <label for="telefone">Telefone</label>
      <svg class="icon-left" viewBox="0 0 24 24"><path d="M6.62 10.79a15 15 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.11-.21 12 12 0 0 0 3.88.76 1 1 0 0 1 1 1v3.5a1 1 0 0 1-1 1C7.16 21.63 2.37 16.84 2.37 12a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.35.27 2.67.76 3.88a1 1 0 0 1-.21 1.11l-2.8 2.8z"/></svg>
    </div>

    <div class="input-group">
      <textarea name="endereco" id="endereco" placeholder=" " required></textarea>
      <label for="endereco">Endereço</label>
      <svg class="icon-left" viewBox="0 0 24 24" style="top:22px"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 11.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>
    </div>

    <div class="input-group">
      <input type="password" name="senha" id="senha" placeholder=" " required />
      <label for="senha">Senha</label>
      <svg class="icon-left" viewBox="0 0 24 24"><path d="M12 17a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm6-7h-1V7a5 5 0 0 0-10 0v3H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2zM8 7a4 4 0 0 1 8 0v3H8V7z"/></svg>
      <svg id="toggleSenha" class="toggle-password" viewBox="0 0 24 24" onclick="togglePassword()">
        <path id="eyePath" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17a5 5 0 110-10 5 5 0 010 10z"/>
      </svg>
    </div>

    <button type="submit">Cadastrar</button>
  </form>
</main>

<script>
const eyeOpen = "M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17a5 5 0 110-10 5 5 0 010 10z";
const eyeClosed = "M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17a5 5 0 110-10 5 5 0 010 10z";

function togglePassword(){
  const input = document.getElementById('senha');
  const eyePath = document.getElementById('eyePath');
  if (input.type === 'password') {
    input.type = 'text';
    eyePath.setAttribute('d', eyeClosed);
  } else {
    input.type = 'password';
    eyePath.setAttribute('d', eyeOpen);
  }
}
</script>
</body>
</html>
