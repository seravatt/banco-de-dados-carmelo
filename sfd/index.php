<?php
session_start();
include 'conexao.php';
$logado = isset($_SESSION['usuario_id']);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔥 Bruiser Build - Periféricos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600&display=swap" rel="stylesheet">
    <style>
       #intro {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, #111 0%, #000 100%);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    animation: introFadeOut 1.5s ease 1s forwards; 
}

#intro img {
    width: 250px;
    opacity: 0;
    transform: scale(0.8);
    animation: logoIntro 1.8s ease forwards;
}

@keyframes logoIntro {
    0% { opacity: 0; transform: scale(0.8); filter: blur(8px); }
    50% { opacity: 1; transform: scale(1.05); filter: blur(0); }
    100% { opacity: 1; transform: scale(1); }
}

@keyframes introFadeOut {
    to { opacity: 0; visibility: hidden; }
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

        .search-bar {
            margin-top: 80px;
            text-align: center;
        }

        .search-bar input[type="text"] {
            padding: 10px;
            width: 60%;
            border-radius: 8px;
            border: none;
            background-color: #222;
            color: #fff;
        }

        .search-bar input[type="submit"] {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            background-color: #f0541e;
            color: #fff;
            cursor: pointer;
        }

        .filtros {
            margin: 30px auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }

        .filtros button {
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            background-color: #222;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .filtros button:hover {
            background-color: #f0541e;
            transform: scale(1.05);
        }

        .carrossel {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 40px 20px;
        }

        .produto {
            width: 240px;
            background-color: #1a1a1a;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 69, 0, 0.2);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .produto:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(255, 69, 0, 0.5);
        }

        .produto img {
            width: 100%;
            height: 180px;
            object-fit: contain;
            margin-bottom: 10px;
            border-radius: 20px;
        }

        .produto h3 {
            color: #f0541e;
            font-size: 18px;
        }

        .produto p {
            font-size: 14px;
            margin: 8px 0;
        }

        .produto .preco {
            color: #f0541e;
            font-size: 16px;
            font-weight: bold;
        }

        .produto button {
            margin-top: 10px;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            background-color: #f0541e;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .produto button:hover {
            background-color: #ff3300;
        }

        .popup {
            position: fixed;
            top: 80px;
            right: 30px;
            background-color: #1f1f1f;
            border-left: 5px solid #f0541e;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            box-shadow: 0 0 5px rgba(255, 69, 0, 0.4);
            opacity: 0;
            transform: translateX(100%);
            animation: fadeInSlide 0.5s forwards;
        }

        @keyframes fadeInSlide {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>
<div id="intro">
    <img src="imagens/logo-bruiser.png" alt="Bruiser Build">
</div>
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


    <div class="search-bar">
        <form method="GET" action="index.php">
            <input type="text" name="buscar" placeholder="🔍 Buscar produto...">
            <input type="submit" value="Buscar">
        </form>
    </div>

    <div class="filtros">
        <button onclick="filtrarProdutos('todos')">Todos</button>
        <button onclick="filtrarProdutos('Teclado')">Teclado</button>
        <button onclick="filtrarProdutos('Mouse')">Mouse</button>
        <button onclick="filtrarProdutos('Headset')">Headset</button>
        <button onclick="filtrarProdutos('Monitores')">Monitores</button>
        <button onclick="filtrarProdutos('Cadeiras')">Cadeiras</button>
        <button onclick="filtrarProdutos('Mousepads')">Mousepads</button>
        <button onclick="filtrarProdutos('Acessórios')">Acessórios</button>
    </div>

    <?php
    $termo = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
    $sql = "SELECT * FROM produtos";

    if (!empty($termo)) {
        $sql .= " WHERE nome LIKE '%" . $conn->real_escape_string($termo) . "%' 
                  OR descricao LIKE '%" . $conn->real_escape_string($termo) . "%'";
    }

    $result = $conn->query($sql);
    ?>

    <div class="carrossel">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="produto" data-categoria="<?= $row['categoria'] ?>" onclick="window.location.href='produto.php?id=<?= $row['id'] ?>'">
    <img src="<?= $row['imagem'] ?>" alt="<?= htmlspecialchars($row['nome']) ?>" style="cursor: pointer;">
    <h3 style="cursor: pointer;"><?= htmlspecialchars($row['nome']) ?></h3>
    <p><?= htmlspecialchars($row['descricao']) ?></p>
    <p class="preco">R$ <?= number_format($row['preco'], 2, ',', '.') ?></p>

                <button
    <?php if (!isset($_SESSION['usuario_id'])): ?>
        onclick="alert('⚠️ Você precisa estar logado para adicionar produtos ao carrinho!'); return false;" 
        style="cursor: not-allowed; background-color: #555;"
    <?php else: ?>
        onclick='adicionarAoCarrinho({
            id: <?= $row["id"] ?>,
            nome: "<?= addslashes($row["nome"]) ?>",
            preco: <?= $row["preco"] ?>,
            imagem: "<?= $row["imagem"] ?>",
            quantidade: 1
        })'
    <?php endif; ?>
>
    Adicionar ao Carrinho
</button>

<button
    <?php if (!isset($_SESSION['usuario_id'])): ?>
        onclick="alert('⚠️ Você precisa estar logado para favoritar produtos!'); return false;" 
        style="cursor: not-allowed; background-color: #555;"
    <?php else: ?>
        onclick='adicionarAosFavoritos({
            id: <?= $row["id"] ?>,
            nome: "<?= addslashes($row["nome"]) ?>",
            preco: <?= $row["preco"] ?>,
            imagem: "<?= $row["imagem"] ?>"
        })'
    <?php endif; ?>
>
    ❤️ Favoritar
</button>         
</div>
        <?php endwhile; ?>
    </div>

    <?php $conn->close(); ?>
    <button id="btnTopo" title="Voltar ao topo">
    <i class="fas fa-chevron-up"></i>
</button> 

    <script>
        
        const usuarioLogado = <?= isset($_SESSION['usuario_id']) ? 'true' : 'false' ?>;
        function adicionarAoCarrinho(produto) {
    if (!usuarioLogado) {
        alert("⚠️ Você precisa estar logado para adicionar itens ao carrinho.");
        window.location.href = "login.php";
        return;
    }

    let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    let itemExistente = carrinho.find(p => p.id === produto.id);

    if (itemExistente) {
        itemExistente.quantidade += 1;
    } else {
        carrinho.push(produto);
    }

    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    mostrarPopup(produto.nome);
}
        function adicionarAoCarrinho(produto) {
            let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
            let itemExistente = carrinho.find(p => p.id === produto.id);

            if (itemExistente) {
                itemExistente.quantidade += 1;
            } else {
                carrinho.push(produto);
            }

            localStorage.setItem('carrinho', JSON.stringify(carrinho));
            mostrarPopup(produto.nome);
        }

        function mostrarPopup(produtoNome) {
            const popup = document.createElement('div');
            popup.classList.add('popup');
            popup.innerHTML = `🔥 <strong>${produtoNome}</strong> adicionado ao carrinho!`;
            document.body.appendChild(popup);

            setTimeout(() => {
                popup.remove();
            }, 3500);
        }

        function filtrarProdutos(categoria) {
            const produtos = document.querySelectorAll('.produto');

            produtos.forEach(produto => {
                const cat = produto.getAttribute('data-categoria');
                if (categoria === 'todos' || cat === categoria) {
                    produto.style.display = 'block';
                } else {
                    produto.style.display = 'none';
                }
            });
        }
        window.addEventListener("scroll", function () {
    const btn = document.getElementById("btnTopo");
    if (window.scrollY > 500) {
        btn.style.display = "block";
    } else {
        btn.style.display = "none";
    }
});


document.getElementById("btnTopo").addEventListener("click", function () {
    window.scrollTo({
        top: 1,
        behavior: 'smooth'
    });
     function mostrarPopupLogin() {
    const popup = document.getElementById('popup-login-obrigatorio');
    popup.style.display = 'block';
  }

  function fecharPopupLogin() {
    const popup = document.getElementById('popup-login-obrigatorio');
    popup.style.display = 'none';
  }
});
document.addEventListener("DOMContentLoaded", () => {
    setTimeout(() => {
        document.getElementById("intro").style.display = "none";
    }, 2500); // 2s animação + 2.5s fade
});
function adicionarAosFavoritos(produto) {
    if (!usuarioLogado) {
        alert("⚠️ Você precisa estar logado para favoritar itens.");
        window.location.href = "login.php";
        return;
    }

    let favoritos = JSON.parse(localStorage.getItem('favoritos')) || [];
    let existe = favoritos.find(p => p.id === produto.id);

    if (!existe) {
        favoritos.push(produto);
        localStorage.setItem('favoritos', JSON.stringify(favoritos));
        mostrarPopupFavorito(produto.nome);
    } else {
        alert("⚠️ Esse produto já está nos seus favoritos!");
    }
}

function mostrarPopupFavorito(produtoNome) {
    const popup = document.createElement('div');
    popup.classList.add('popup');
    popup.innerHTML = `❤️ <strong>${produtoNome}</strong> adicionado aos favoritos!`;
    document.body.appendChild(popup);

    setTimeout(() => {
        popup.remove();
    }, 3500);
}
   </script>

</body>
</html>