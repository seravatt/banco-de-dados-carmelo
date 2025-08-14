<?php
session_start();
include 'conexao.php';


if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$logado = true;
?>



<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Meus Favoritos - Bruiser Build</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap');

:root {
    --cor-fundo: #121212;
    --cor-card: #1f1f1f;
    --cor-primaria: #e73e00;
    --cor-preco: #ff4d00;
    --cor-texto: #fff;
    --cor-secundaria: #ccc;
}

* {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Josefin Sans', sans-serif;
            background-color: #111;
            color: #fff;
        }
        #btnTopo {
    display: none;
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1001;
    background: linear-gradient(145deg, #ff3300, #f0541e);
    color: #fff;
    border: none;
    outline: none;
    width: 50px;
    height: 50px;
    font-size: 22px;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 0 15px rgba(255, 69, 0, 0.5);
    transition: transform 0.3s ease, opacity 0.3s ease;
}

#btnTopo:hover {
    transform: scale(1.1);
    background: linear-gradient(145deg, #f0541e, #ff3300);
}

        header {
            background-color: #111;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 30px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
       
        }

        header .logo {
            font-size: 8px;
            font-weight: 100;
           
            text-decoration: none;
        }

        nav a {
            margin-left: 20px;
            color: #fff;
            text-decoration: none;
            transition: color 0.3s;
        }

        nav a:hover {
            color: #f0541e;
        }

.container {
    padding: 100px 20px 40px;
    max-width: 1200px;
    margin: auto;
}
h1 {
    text-align: center;
    margin-bottom: 30px;
    color: var(--cor-primaria);
}


.produtos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
}
.produto-card {
    background: var(--cor-card);
    border-radius: 15px;
    padding: 15px;
    text-align: center;
    box-shadow: 0 6px 20px rgba(0,0,0,0.35);
    transform: translateY(30px);
    opacity: 0;
    animation: fadeUp 0.8s ease forwards;
}
.produto-card img {
    max-width: 100%;
    border-radius: 10px;
    margin-bottom: 10px;
}
.produto-card h3 {
    font-size: 1.2rem;
    color: var(--cor-primaria);
    margin: 10px 0;
}
.produto-card p {
    font-size: 1rem;
    font-weight: bold;
    color: var(--cor-preco);
}
.produto-card a.btn,
.produto-card button.btn {
    display: inline-block;
    padding: 8px 12px;
    margin-top: 10px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #fff;
    background: var(--cor-primaria);
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s;
}
.produto-card a.btn:hover,
.produto-card button.btn:hover {
    background: var(--cor-preco);
}
button.btn.remove {
    background: #6a1a1a;
}
button.btn.remove:hover {
    background: #ff4d00;
}


@keyframes fadeUp {
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
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
    <h1>❤️ Meus Favoritos</h1>
    <div id="favoritosLista" class="produtos-grid"></div>
    <p id="semFavoritos" style="display:none; text-align:center; color: var(--cor-secundaria);">Nenhum favorito encontrado 🐾</p>
</div>

<footer style="text-align:center; padding:20px; color:var(--cor-secundaria); font-size:0.9rem;">
    &copy; <?= date('Y') ?> Bruiser Build - Todos os direitos reservados
</footer>

<script>

document.addEventListener("DOMContentLoaded", function(){
    let favoritos = JSON.parse(localStorage.getItem('favoritos')) || [];

    if(favoritos.length === 0){
        document.getElementById('semFavoritos').style.display = 'block';
        return;
    }

    let container = document.getElementById('favoritosLista');
    favoritos.forEach((prod, i) => {
        let card = document.createElement('div');
        card.className = 'produto-card';
        card.style.animationDelay = `${i * 0.1}s`; 
        card.innerHTML = `
            <img src="${prod.imagem}" alt="${prod.nome}">
            <h3>${prod.nome}</h3>
            <p>R$ ${Number(prod.preco).toFixed(2).replace('.', ',')}</p>
            <a href="produto.php?id=${prod.id}" class="btn">Ver Produto</a>
            <button onclick="removerFavorito(${prod.id})" class="btn remove">Remover</button>
        `;
        container.appendChild(card);
    });
});


function removerFavorito(id) {
    let favoritos = JSON.parse(localStorage.getItem('favoritos')) || [];
    favoritos = favoritos.filter(p => p.id !== id);
    localStorage.setItem('favoritos', JSON.stringify(favoritos));
    location.reload();
}
</script>

</body>
</html>
