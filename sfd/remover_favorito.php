<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produto_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $produto_id = (int) $_POST['produto_id'];

    $pdo = new PDO("mysql:host=localhost;dbname=sfd;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "DELETE FROM favoritos WHERE usuario_id = ? AND produto_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $produto_id]);
}

header("Location: favoritos.php");
exit();