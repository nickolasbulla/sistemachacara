<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/init.php';

$titulo_pagina = "Vistoria - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";
include '../../includes/layout/deletemodal.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    csrf_verify();
    $id = (int) $_POST['delete_id'];
    $info = $conn->prepare("SELECT nome_item FROM itens_vistoria WHERE id_item_vistoria = ?");
    $info->bind_param("i", $id);
    $info->execute();
    $row = $info->get_result()->fetch_assoc();
    $detalhes = $row ? "nome: {$row['nome_item']}" : null;

    try {
        $stmt = $conn->prepare("DELETE FROM itens_vistoria WHERE id_item_vistoria = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        registrar_log($conn, $_SESSION['usuario_id'], 'excluir', 'item_vistoria', $id, $detalhes);
        header("Location: index.php?deletado=1");
        exit;
    } catch (mysqli_sql_exception $e) {
        header("Location: index.php?erro_relacionado=1");
        exit;
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
            <h1>Itens de Vistoria</h1>
        </header>

        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alerta sucesso">Item cadastrado com sucesso!</div>
        <?php elseif (isset($_GET['editado'])): ?>
            <div class="alerta sucesso">Item atualizado com sucesso!</div>
        <?php elseif (isset($_GET['deletado'])): ?>
            <div class="alerta sucesso">Item excluído com sucesso!</div>
        <?php endif; ?>

        <?php if (isset($_GET['erro_relacionado'])): ?>
            <div class="alerta erro">Não é possível excluir: este item está vinculado a uma ou mais vistorias.</div>
        <?php endif; ?>

        <div class="area-crud">
            <a href="./create.php" class="btn btn-novo">
                <i class="fa-solid fa-plus"></i>
                Novo Item
            </a>

            <?php
            $query = "SELECT * FROM itens_vistoria ORDER BY id_item_vistoria DESC";
            $result = $conn->query($query);
            ?>
            <div class="tabela-wrapper">
                <table class="tabela-crud">
                    <thead>
                        <tr>
                            <th>Nome do Item</th>
                            <th>Descrição</th>
                            <th>Ativo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Nome"><?= htmlspecialchars($row['nome_item']) ?></td>
                                <td data-label="Descrição"><?= htmlspecialchars($row['descricao'] ?? '-') ?></td>
                                <td data-label="Ativo">
                                    <?= $row['ativo'] ? '<i class="fa-solid fa-check"></i>' : '<i class="fa-solid fa-xmark"></i>' ?>
                                </td>
                                <td data-label="Ações">
                                    <a href="./edit.php?id=<?= $row['id_item_vistoria'] ?>" class="btn-editar">
                                        <i class="fa-solid fa-hand-pointer"></i>
                                        Selecionar
                                    </a>
                                    <a href="#" class="btn-excluir btnpopup" data-id="<?= $row['id_item_vistoria'] ?>">
                                        <i class="fa-solid fa-trash"></i>
                                        Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include '../../includes/layout/footer.php'; ?>