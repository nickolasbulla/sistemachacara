<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/init.php';

$titulo_pagina = "Itens de Vistoria - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";

$id   = $_GET['id'] ?? null;
$erro = '';

if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM itens_vistoria WHERE id_item_vistoria = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $nome_item = $_POST['nome_item'];
    $descricao = $_POST['descricao'];
    $ativo     = isset($_POST['ativo']) ? 1 : 0;

    $check = $conn->prepare("SELECT id_item_vistoria FROM itens_vistoria WHERE nome_item = ? AND id_item_vistoria != ?");
    $check->bind_param("si", $nome_item, $id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $erro = "Já existe outro item com esse nome.";
    } else {
        $stmt = $conn->prepare("UPDATE itens_vistoria SET nome_item = ?, descricao = ?, ativo = ? WHERE id_item_vistoria = ?");
        $stmt->bind_param("ssii", $nome_item, $descricao, $ativo, $id);

        if ($stmt->execute()) {
            registrar_log($conn, $_SESSION['usuario_id'], 'editar', 'item_vistoria', (int) $id);
            header("Location: index.php?editado=1");
            exit;
        } else {
            $erro = "Erro ao atualizar o item.";
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
            <h1>Editar Item de Vistoria</h1>
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
                <?= csrf_field() ?>
                <div class="form-grupo">
                    <label>Nome do item: *</label>
                    <input type="text" name="nome_item" value="<?= htmlspecialchars($item['nome_item']) ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Descrição:</label>
                    <textarea name="descricao" rows="3"><?= htmlspecialchars($item['descricao'] ?? '') ?></textarea>
                </div>

                <div class="form-grupo checkbox">
                    <label class="checkbox-container">
                        <input type="checkbox" name="ativo" <?= $item['ativo'] ? 'checked' : '' ?>>
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