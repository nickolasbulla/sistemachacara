<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/init.php';

$titulo_pagina = "Bloqueios - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";
include '../../includes/layout/deletemodal.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    csrf_verify();
    $id = (int) $_POST['delete_id'];

    $info = $conn->prepare("SELECT data_inicio, data_fim, motivo FROM bloqueios WHERE id_bloqueio = ?");
    $info->bind_param("i", $id);
    $info->execute();
    $row = $info->get_result()->fetch_assoc();
    $detalhes = $row ? "período: {$row['data_inicio']} a {$row['data_fim']} | motivo: {$row['motivo']}" : null;

    try {
        $stmt = $conn->prepare("DELETE FROM bloqueios WHERE id_bloqueio = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        registrar_log($conn, $_SESSION['usuario_id'], 'excluir', 'bloqueio', $id, $detalhes);

        header("Location: index.php?deletado=1");
        exit;
    } catch (mysqli_sql_exception $e) {
        header("Location: index.php?erro_relacionado=1");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    csrf_verify();
    $id = (int) $_POST['toggle_id'];
    $stmt = $conn->prepare("UPDATE bloqueios SET ativo = IF(ativo = 1, 0, 1) WHERE id_bloqueio = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: index.php");
    exit;
}

$query = "
    SELECT b.*, u.nome_completo
    FROM bloqueios b
    LEFT JOIN usuarios u ON u.id_usuario = b.id_usuario
    ORDER BY b.data_inicio DESC
";
$result = $conn->query($query);
?>

<div class="painel-container">

    <?php include '../../includes/layout/sidebar.php'; ?>
    <div class="sidebar-overlay"></div>

    <main class="conteudo">
        <header class="painel-header">
            <button class="menu-toggle">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1>Bloqueios</h1>
        </header>

        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alerta sucesso">Bloqueio cadastrado com sucesso!</div>
        <?php elseif (isset($_GET['editado'])): ?>
            <div class="alerta sucesso">Bloqueio atualizado com sucesso!</div>
        <?php elseif (isset($_GET['deletado'])): ?>
            <div class="alerta sucesso">Bloqueio removido com sucesso!</div>
        <?php endif; ?>

        <?php if (isset($_GET['erro_relacionado'])): ?>
            <div class="alerta erro">Não foi possível excluir este bloqueio.</div>
        <?php endif; ?>

        <div class="area-crud">
            <a href="./create.php" class="btn btn-novo">
                <i class="fa-solid fa-plus"></i>
                Novo Bloqueio
            </a>

            <div class="tabela-wrapper">
                <table class="tabela-crud">
                    <thead>
                        <tr>
                            <th>Período</th>
                            <th>Motivo</th>
                            <th>Cadastrado por</th>
                            <th>Ativo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Período">
                                    <?= date('d/m/Y', strtotime($row['data_inicio'])) ?>
                                    &rarr;
                                    <?= date('d/m/Y', strtotime($row['data_fim'])) ?>
                                </td>
                                <td data-label="Motivo"><?= htmlspecialchars($row['motivo']) ?></td>
                                <td data-label="Cadastrado por"><?= htmlspecialchars($row['nome_completo']) ?></td>
                                <td data-label="Ativo">
                                    <form method="POST" style="display:inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="toggle_id" value="<?= $row['id_bloqueio'] ?>">
                                        <button type="submit" class="btn-toggle-ativo <?= $row['ativo'] ? 'ativo' : 'inativo' ?>" title="<?= $row['ativo'] ? 'Clique para desativar' : 'Clique para ativar' ?>">
                                            <i class="fa-solid <?= $row['ativo'] ? 'fa-check' : 'fa-xmark' ?>"></i>
                                        </button>
                                    </form>
                                </td>
                                <td data-label="Ações">
                                    <a href="./edit.php?id=<?= $row['id_bloqueio'] ?>" class="btn-editar">
                                        <i class="fa-solid fa-hand-pointer"></i>
                                        Selecionar
                                    </a>
                                    <a href="#" class="btn-excluir btnpopup" data-id="<?= $row['id_bloqueio'] ?>">
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