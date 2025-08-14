<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php?erro=precisa_logar");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Meu Carrinho - Bruiser Build</title>
<style>
    body {
        background-color: #121212;
        color: #fff;
        font-family: 'Segoe UI', sans-serif;
        padding: 30px;
    }
    header .logo img {
        height: 90px;
        cursor: pointer;
    }
    h1 {
        text-align: center;
        color: #ff3c00;
        margin-bottom: 30px;
        font-size: 2.2rem;
    }
    .carrinho {
        max-width: 900px;
        margin: auto;
        background-color: #1f1f1f;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 0 15px rgba(255, 60, 0, 0.3);
    }
    .item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #333;
        padding: 15px 0;
        gap: 15px;
    }
    .item img {
        width: 90px;
        height: 90px;
        object-fit: contain;
        border-radius: 8px;
        background-color: #2a2a2a;
        padding: 5px;
    }
    .item-info {
        flex: 1;
    }
    .item-info h3 {
        color: #ff3c00;
        margin-bottom: 5px;
        font-size: 1.2rem;
    }
    .item-info p {
        margin: 3px 0;
    }
    .quantidade {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .quantidade button {
        background-color: #ff3c00;
        border: none;
        color: white;
        padding: 5px 10px;
        font-size: 18px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color .2s;
    }
    .quantidade button:hover {
        background-color: #d93200;
    }
    .quantidade input {
        width: 50px;
        text-align: center;
        border: none;
        border-radius: 4px;
        padding: 5px;
        background-color: #2a2a2a;
        color: #fff;
        font-size: 1rem;
    }
    .remover {
        background-color: #ff1e1e;
        border: none;
        color: white;
        padding: 6px 12px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color .2s;
    }
    .remover:hover {
        background-color: #d40000;
    }
    .total {
        text-align: right;
        font-size: 20px;
        margin-top: 20px;
        padding-top: 10px;
        border-top: 2px solid #333;
    }
    .finalizar {
        display: block;
        background-color: #ff3c00;
        color: #fff;
        padding: 15px;
        text-align: center;
        border-radius: 8px;
        margin-top: 20px;
        text-decoration: none;
        font-size: 18px;
        font-weight: bold;
        transition: background-color .3s;
    }
    .finalizar:hover {
        background-color: #d93200;
    }
    .vazio {
        text-align: center;
        color: #aaa;
        font-size: 1.2rem;
        padding: 20px;
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

<h1>🛒 Meu Carrinho</h1>

<div class="carrinho"></div>

<script>
let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];

function renderCarrinho() {
    const carrinhoContainer = document.querySelector('.carrinho');
    carrinhoContainer.innerHTML = '';

    if (carrinho.length === 0) {
        carrinhoContainer.innerHTML = '<p class="vazio">Seu carrinho está vazio.</p>';
        return;
    }

    let total = 0;

    carrinho.forEach(item => {
        total += item.preco * item.quantidade;

        carrinhoContainer.innerHTML += `
        <div class="item">
            <img src="${item.imagem}" alt="${item.nome}">
            <div class="item-info">
                <h3>${item.nome}</h3>
                <p>Preço unitário: R$ ${item.preco.toFixed(2)}</p>
                <div class="quantidade">
                    <button onclick="alterarQuantidade(${item.id}, -1)">-</button>
                    <input type="number" value="${item.quantidade}" min="1" onchange="alterarQtdDireto(${item.id}, this.value)">
                    <button onclick="alterarQuantidade(${item.id}, 1)">+</button>
                </div>
            </div>
            <button class="remover" onclick="removerItem(${item.id})">Remover</button>
        </div>`;
    });

    carrinhoContainer.innerHTML += `
        <div class="total">
            <strong>Total: R$ ${total.toFixed(2)}</strong>
        </div>
        <a href="#" class="finalizar">Finalizar Compra</a>
    `;
}

function alterarQuantidade(id, delta) {
    let item = carrinho.find(p => p.id === id);
    if (item) {
        item.quantidade += delta;
        if (item.quantidade <= 0) {
            carrinho = carrinho.filter(p => p.id !== id);
        }
        localStorage.setItem('carrinho', JSON.stringify(carrinho));
        renderCarrinho();
    }
}

function alterarQtdDireto(id, valor) {
    let item = carrinho.find(p => p.id === id);
    if (item) {
        item.quantidade = parseInt(valor) || 1;
        localStorage.setItem('carrinho', JSON.stringify(carrinho));
        renderCarrinho();
    }
}

function removerItem(id) {
    carrinho = carrinho.filter(item => item.id != id);
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    renderCarrinho();
}

renderCarrinho();
</script>
</body>
</html>
