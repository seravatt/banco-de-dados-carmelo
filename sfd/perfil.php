<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];


$sql = "SELECT nome, email, telefone, endereco FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$usuario = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<title>Meu Perfil - Bruiser Build</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
    :root {
        --bg: #0f0f0f;
        --card: #1b1b1b;
        --accent: #ff4500;
        --accent-hover: #ff3300;
        --danger: #e73e00;
        --danger-hover: #ff0000;
        --info: #0f62fe;
        --text-muted: #b0b0b0;
    }
    body {
        background-color: var(--bg);
        color: #fff;
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
        padding: 0;
    }
    header {
        position: fixed;
        top: 0; left: 0; right: 0;
        z-index: 1000;
        background-color: var(--bg);
        padding: 10px 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.4);
    }
    .logo img {
        height: 70px;
        transition: transform 0.2s;
    }
    .logo img:hover {
        transform: scale(1.05);
    }
    main {
        max-width: 650px;
        margin: 120px auto 40px auto;
        background-color: var(--card);
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.6);
        animation: fadeIn 0.4s ease;
    }
    h1 {
        color: var(--accent);
        margin-bottom: 25px;
        text-align: center;
        font-size: 26px;
    }
    .perfil-info {
        margin-bottom: 25px;
    }
    .perfil-info p {
        font-size: 16px;
        margin: 12px 0;
        padding: 12px;
        background: #222;
        border-radius: 8px;
        transition: background 0.2s;
    }
    .perfil-info p:hover {
        background: #2d2d2d;
    }
    .perfil-info strong {
        color: var(--accent);
        display: inline-block;
        min-width: 110px;
    }
    .btn {
        display: block;
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 6px;
        font-size: 15px;
        font-weight: bold;
        color: #fff;
        cursor: pointer;
        margin-top: 12px;
        transition: background 0.2s, transform 0.1s;
    }
    .btn:active {
        transform: scale(0.98);
    }
    .btn-danger {
        background-color: var(--danger);
    }
    .btn-danger:hover {
        background-color: var(--danger-hover);
    }
    .btn-info {
        background-color: var(--info);
    }
    .btn-info:hover {
        background-color: #0043ce;
    }
    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(10px);}
        to {opacity: 1; transform: translateY(0);}
    }
</style>
</head>
<body>
<header>
    <div class="logo">
        <a href="index.php">
            <img src="imagens/logo-bruiser.png" alt="Bruiser Build">
        </a>
    </div>
</header>

<main>
    <h1>Meu Perfil</h1>
    <div class="perfil-info">
        <p><strong>Nome:</strong> <?= htmlspecialchars($usuario['nome']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
        <p><strong>Telefone:</strong> <?= htmlspecialchars($usuario['telefone']) ?></p>
        <p><strong>Endereço:</strong> <?= htmlspecialchars($usuario['endereco']) ?></p>
    </div>

    <form method="post" action="logout.php">
        <button type="submit" class="btn btn-danger">Sair da Conta</button>
    </form>

    <form action="editar_perfil.php" method="get">
        <button type="submit" class="btn btn-info">Editar Perfil</button>
    </form>
</main>
</body>
</html>
