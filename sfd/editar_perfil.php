<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

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

$usuario_id = $_SESSION['usuario_id'];
$mensagem = '';
$tipoMensagem = '';


try {
    $stmt = $pdo->prepare("SELECT nome, email, telefone, endereco FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$usuario) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
} catch (PDOException $e) {
    die("Erro ao buscar dados do usuário: " . $e->getMessage());
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];
    $senhaNova = $_POST['senha'];

    try {
        
        $sql = "UPDATE usuarios SET nome = ?, email = ?, telefone = ?, endereco = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $email, $telefone, $endereco, $usuario_id]);

       
        if (!empty($senhaNova)) {
            $senhaHash = password_hash($senhaNova, PASSWORD_DEFAULT);
            $sqlSenha = "UPDATE login SET senha = ? WHERE usuario_id = ?";
            $stmtSenha = $pdo->prepare($sqlSenha);
            $stmtSenha->execute([$senhaHash, $usuario_id]);
        }

        $mensagem = "Dados atualizados com sucesso!";
        $tipoMensagem = 'sucesso';

      
        $usuario['nome'] = $nome;
        $usuario['email'] = $email;
        $usuario['telefone'] = $telefone;
        $usuario['endereco'] = $endereco;

    } catch (PDOException $e) {
        $mensagem = "Erro ao atualizar dados: " . $e->getMessage();
        $tipoMensagem = 'erro';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<title>Editar Perfil - Bruiser Build</title>
<style>
    :root {
        --bg: #0f0f0f;
        --card: #1b1b1b;
        --input-bg: #2a2a2a;
        --accent: #ff4500;
        --accent-hover: #ff3300;
        --success: #00c853;
        --error: #ff3b3b;
        --text-muted: #b0b0b0;
    }
    body {
        background: var(--bg);
        color: #fff;
        font-family: 'Segoe UI', sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
    }
    .editar-box {
        background: var(--card);
        padding: 30px;
        border-radius: 12px;
        width: 100%;
        max-width: 460px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.6);
        animation: fadeIn 0.4s ease;
    }
    .editar-box h2 {
        color: var(--accent);
        text-align: center;
        margin-bottom: 20px;
        font-size: 22px;
    }
    .editar-box label {
        display: block;
        margin: 12px 0 6px;
        font-size: 14px;
        color: var(--text-muted);
    }
    .editar-box input, .editar-box textarea {
        width: 100%;
        padding: 11px;
        border: none;
        border-radius: 6px;
        background: var(--input-bg);
        color: #fff;
        outline: none;
        transition: 0.2s;
    }
    .editar-box input:focus, .editar-box textarea:focus {
        box-shadow: 0 0 0 2px var(--accent);
    }
    .editar-box button {
        margin-top: 22px;
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 6px;
        background: var(--accent);
        color: #fff;
        font-size: 15px;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.2s;
    }
    .editar-box button:hover {
        background: var(--accent-hover);
    }
    .mensagem {
        text-align: center;
        margin-bottom: 15px;
        font-weight: bold;
        padding: 10px;
        border-radius: 6px;
    }
    .mensagem.sucesso {
        background: var(--success);
        color: #fff;
    }
    .mensagem.erro {
        background: var(--error);
        color: #fff;
    }
    .voltar-link {
        display: block;
        text-align: center;
        margin-top: 18px;
        color: var(--accent);
        text-decoration: none;
        font-size: 14px;
    }
    .voltar-link:hover {
        text-decoration: underline;
    }
    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(10px);}
        to {opacity: 1; transform: translateY(0);}
    }
</style>
</head>
<body>
    <div class="editar-box">
        <h2>Editar Perfil</h2>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?= htmlspecialchars($tipoMensagem) ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <label for="nome">Nome Completo</label>
            <input type="text" id="nome" name="nome" required pattern="[A-Za-z\s]+" 
                title="O nome deve conter apenas letras e espaços." 
                value="<?= htmlspecialchars($usuario['nome']) ?>">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required maxlength="100" 
                value="<?= htmlspecialchars($usuario['email']) ?>">

            <label for="telefone">Telefone</label>
            <input type="tel" id="telefone" name="telefone" required pattern="\d{10,11}" 
                title="O telefone deve conter apenas números e ter entre 10 a 11 dígitos." 
                value="<?= htmlspecialchars($usuario['telefone']) ?>">

            <label for="endereco">Endereço</label>
            <textarea id="endereco" name="endereco" rows="3" required><?= htmlspecialchars($usuario['endereco']) ?></textarea>

            <label for="senha">Nova Senha <span style="color:var(--text-muted)">(opcional)</span></label>
            <input type="password" id="senha" name="senha" placeholder="Digite para alterar a senha">

            <button type="submit">Salvar Alterações</button>
        </form>

        <a href="perfil.php" class="voltar-link">← Voltar para o Perfil</a>
    </div>
</body>
</html>
