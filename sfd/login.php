<?php
session_start();

$host = 'localhost';
$dbname = 'sfd';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT u.id AS usuario_id, u.nivel, l.senha 
            FROM usuarios u 
            JOIN login l ON u.id = l.usuario_id 
            WHERE u.email = ?";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['usuario_id'];
            $_SESSION['usuario'] = $email;
            $_SESSION['nivel'] = $usuario['nivel'];

            if ($usuario['nivel'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: perfil.php");
            }
            exit();
        } else {
            $mensagem = "Email ou senha incorretos.";
        }
    } catch (PDOException $e) {
        $mensagem = "Erro no sistema: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Login - Bruiser Build</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap');
    *{box-sizing:border-box} body{margin:0}

    body {
        background-color:#121212;color:#ddd;display:flex;justify-content:center;align-items:center;
        min-height:100vh;padding:20px;font-family:'Montserrat',sans-serif;
    }

  
@keyframes slideFadeIn {
    0% {opacity: 0; transform: translateY(30px);}
    100% {opacity: 1; transform: translateY(0);}
}


@keyframes fadeUp {
    0% {opacity: 0; transform: translateY(20px);}
    100% {opacity: 1; transform: translateY(0);}
}

.link-cadastro {
    text-align: center;
    margin-top: 15px;
    font-size: 0.9rem;
    color: #aaa;
}

.link-cadastro a {
    color: #e73e00;
    text-decoration: none;
    font-weight: bold;
}

.link-cadastro a:hover {
    text-decoration: underline;
}
main {
    animation: slideFadeIn 0.8s ease forwards;
}


.input-group {
    opacity: 0;
    animation: fadeUp 0.6s ease forwards;
}
.input-group:nth-child(1) { animation-delay: 0.2s; }
.input-group:nth-child(2) { animation-delay: 0.4s; }
.input-group:nth-child(3) { animation-delay: 0.6s; }
.input-group:nth-child(4) { animation-delay: 0.8s; }

button {
    opacity: 0;
    animation: fadeUp 0.6s ease forwards;
    animation-delay: 1s;
}


header {
        position:fixed;top:0;left:0;right:0;height:70px;background:#121212;display:flex;
        justify-content:center;align-items:center;z-index:1000;
    }
    header .logo img {height:60px;cursor:pointer;border-radius:10px}
    main {
        margin-top:100px;width:100%;max-width:420px;background:#1f1f1f;border-radius:20px;
        padding:40px 35px;box-shadow:0 10px 30px rgba(0,0,0,.35);
    }
    h2 {text-align:center;font-size:2rem;color:#e73e00;margin-bottom:25px}

    .mensagem-erro {
        background:#6a1a1a;color:#ffd4d4;padding:12px;border-radius:10px;text-align:center;
        margin-bottom:20px;font-weight:700;
    }

    .input-group{position:relative;margin-bottom:20px}
    .input-group input{
        width:100%;padding:14px 46px;border-radius:12px;border:2px solid #444;background:#222;
        color:#fff;font-size:1rem;outline:none;transition:all .25s ease;
    }
    .input-group input:focus{border-color:#e73e00;background:#2a2a2a}

    .input-group label{
        position:absolute;left:46px;top:50%;transform:translateY(-50%);font-size:.92rem;color:#aaa;
        transition:.25s ease;background:transparent;padding:0 6px;pointer-events:none;
    }
    .input-group input:focus + label,
    .input-group input:not(:placeholder-shown) + label{
        top:-9px;left:12px;font-size:.75rem;color:#e73e00;background:#1f1f1f;border-radius:6px;
    }

    .icon-left{
        position:absolute;left:14px;top:50%;transform:translateY(-50%);
        width:20px;height:20px;fill:#666;pointer-events:none;transition:fill .25s ease;
    }
    .input-group input:focus ~ .icon-left{fill:#e73e00}

    .toggle-password{
        position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;
        width:22px;height:22px;fill:#666;transition:transform .25s, fill .25s;
    }
    .toggle-password:hover{fill:#e73e00;transform:translateY(-50%) rotate(12deg)}
    .eyeClosed{
   color: #ffd4d4;
    }
    button{
        width:100%;padding:15px;border:none;border-radius:12px;background:#e73e00;color:#fff;
        font-size:1.1rem;font-weight:800;cursor:pointer;transition:background .25s ease;
    }
    button:hover{background:#ff4d00}
</style>
</head>
<body>
<header>
  <div class="logo">
    <a href="index.php" title="Bruiser Build — Início"><img src="imagens/logo-bruiser.png" alt="Bruiser Build" /></a>
  </div>
</header>

<main role="main" aria-label="Formulário de login">
  <h2>Login</h2>
  <?php if (!empty($mensagem)): ?>
      <div class="mensagem-erro"><?= htmlspecialchars($mensagem) ?></div>
  <?php endif; ?>

  <form method="POST" autocomplete="off" novalidate>
    <div class="input-group">
      <input type="email" id="email" name="email" placeholder=" " required />
      <label for="email">Email</label>
      <svg class="icon-left" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 
      1.1.9 2 2 2h16a2 2 0 0 0 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5L4 8V6l8 5 8-5v2z"/></svg>
    </div>

    <div class="input-group">
      <input type="password" id="senha" name="senha" placeholder=" " required />
      <label for="senha">Senha</label>
      <svg class="icon-left" viewBox="0 0 24 24"><path d="M12 17a2 2 0 1 0 0-4 2 2 0 0 0 
      0 4zm6-7h-1V7a5 5 0 0 0-10 0v3H6a2 2 0 0 0-2 2v7a2 2 0 0 
      0 2 2h12a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2zM8 7a4 4 0 0 1 
      8 0v3H8V7z"/></svg>
      <svg id="toggleSenha" class="toggle-password" viewBox="0 0 24 24" onclick="togglePassword()">
        <path id="eyePath" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 
        7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 
        17a5 5 0 110-10 5 5 0 010 10z"/>
      </svg>
    </div>

    <button type="submit">Entrar</button>

<p class="link-cadastro">
    Não tem uma conta? <a href="cadastro.php">Cadastre-se</a>
</p>
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
