<?php
session_start();
 
include '../../includes/auth/login_verify.php';
include '../../config/db.php';
 
$titulo_pagina = "Itens de Vistoria - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";
 
$erro = '';
 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome_item    = $_POST["nome_item"];
    $descricao    = $_POST["descricao"];
    $ativo        = isset($_POST["ativo"]) ? 1 : 0;
 
    $check = $conn->prepare("SELECT id_item_vistoria FROM itens_vistoria WHERE nome_item = ?");
    $check->bind_param("s", $nome_item);
    $check->execute();
    $check->store_result();
 
    if ($check->num_rows > 0) {
        $erro = "Já existe um item com esse nome!";
    } else {
        $stmt = $conn->prepare("INSERT INTO itens_vistoria (nome_item, descricao, ativo) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $nome_item, $descricao, $ativo);
 
        if ($stmt->execute()) {
            header("Location: index.php?sucesso=1");
            exit;
        } else {
            $erro = "Erro ao cadastrar o item. Tente novamente.";
        }
    }
}
?>
 
<div class="painel-container">
 
    <?php include '../../includes/layout/sidebar.php'; ?>
    <div class="sidebar-overlay"></div>
 
    <main class="conteudo">
        <header class="painel-header">
            <button class="menu-toggle">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1>Novo Item de Vistoria</h1>
        </header>
 
        <a href="./index.php" class="btn-voltar">
            <i class="fa-solid fa-arrow-left"></i>
            Voltar
        </a>
 
        <div class="cadastro-area">
 
            <?php if (!empty($erro)): ?>
                <div class="alerta erro"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>
 
            <form method="POST" class="form-cadastro">
                <div class="form-grupo">
                    <label>Nome do item: *</label>
                    <input type="text" name="nome_item" required>
                </div>
 
                <div class="form-grupo">
                    <label>Descrição:</label>
                    <textarea name="descricao" rows="3"></textarea>
                </div>
 
                <div class="form-grupo checkbox">
                    <label class="checkbox-container">
                        <input type="checkbox" name="ativo" checked>
                        <span class="checkmark"></span>
                        Item ativo
                    </label>
                </div>
 
                <div class="form-botoes">
                    <button class="btn btn-salvar">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Salvar
                    </button>
                    <a href="./index.php" class="btn btn-cancelar">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>
 
<?php include '../../includes/layout/footer.php'; ?>