<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/init.php';

$titulo_pagina = "Ocorrências - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";

$id_reserva = $_GET['id_reserva'] ?? null;
$erro = '';

if (!$id_reserva) {
    header('Location: index.php');
    exit;
}

// Busca info da reserva
$stmt = $conn->prepare("
    SELECT r.nome_reserva, r.data_reserva, r.telefone_reserva
    FROM reservas r
    WHERE r.id_reserva = ?
");
$stmt->bind_param("i", $id_reserva);
$stmt->execute();
$reserva = $stmt->get_result()->fetch_assoc();

if (!$reserva) {
    header('Location: index.php');
    exit;
}

// Busca todos os itens da ocorrência desta reserva
$stmt2 = $conn->prepare("
    SELECT
        o.id_ocorrencia,
        o.descricao,
        o.status,
        o.data_hora_registro,
        iv.nome_item,
        iv.descricao AS descricao_item,
        u.nome_completo AS usuario_nome,
        vf.foto_url
    FROM ocorrencias o
    INNER JOIN itens_vistoria iv ON iv.id_item_vistoria = o.id_item_vistoria
    INNER JOIN usuarios u ON u.id_usuario = o.id_usuario
    LEFT JOIN vistoria_fotos vf ON vf.id_vistoria_resultado = o.id_vistoria_resultado
    WHERE o.id_reserva = ?
    ORDER BY iv.nome_item
");
$stmt2->bind_param("i", $id_reserva);
$stmt2->execute();
$itens = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($itens)) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $statuses = $_POST['status'] ?? [];

    $status_validos = ['aberta', 'resolvida'];

    $upd = $conn->prepare("UPDATE ocorrencias SET status = ? WHERE id_ocorrencia = ? AND id_reserva = ?");

    $conn->begin_transaction();
    try {
        foreach ($statuses as $id_oc => $status) {
            $id_oc = (int) $id_oc;
            if (!in_array($status, $status_validos, true)) continue;
            $upd->bind_param("sii", $status, $id_oc, $id_reserva);
            $upd->execute();
        }
        $conn->commit();
                registrar_log($conn, $_SESSION['usuario_id'], 'editar', 'ocorrencia', (int) $id_reserva, $reserva['nome_reserva']);
        header("Location: index.php?sucesso=1");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $erro = "Erro ao atualizar as ocorrências.";
    }

    // Recarrega itens atualizados após erro
    $stmt2->execute();
    $itens = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
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
            <h1>Ocorrência</h1>
        </header>

        <a href="./index.php" class="btn-voltar">
            <i class="fa-solid fa-arrow-left"></i>
            Voltar
        </a>

        <div class="cadastro-area">

            <?php if (!empty($erro)): ?>
                <div class="alerta erro"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <!-- Informações da reserva (somente leitura) -->
            <div class="form-cadastro">
                <div class="form-grupo">
                    <label>Reserva:</label>
                    <input type="text" value="<?= htmlspecialchars($reserva['nome_reserva']) ?>" disabled>
                </div>

                <div class="form-grupo">
                    <label>Data da reserva:</label>
                    <input type="text" value="<?= date('d/m/Y', strtotime($reserva['data_reserva'])) ?>" disabled>
                </div>

                <div class="form-grupo">
                    <label>Telefone:</label>
                    <input type="text" value="<?= htmlspecialchars($reserva['telefone_reserva'] ?? '-') ?>" disabled>
                </div>
            </div>

            <!-- Itens da ocorrência (editáveis) -->
            <form method="POST" class="form-cadastro ocorrencia-form-edit">
                <?= csrf_field() ?>
                <?php foreach ($itens as $item): ?>
                    <div class="ocorrencia-item">
                        <div class="ocorrencia-item-header">
                            <strong><?= htmlspecialchars($item['nome_item']) ?></strong>
                            <small>
                                Registrado por <?= htmlspecialchars($item['usuario_nome']) ?>
                                em <?= date('d/m/Y H:i', strtotime($item['data_hora_registro'])) ?>
                            </small>
                        </div>

                        <div class="form-grupo">
                            <label>Descrição</label>
                            <textarea rows="3" disabled><?= htmlspecialchars($item['descricao'] ?? '') ?></textarea>
                        </div>

                        <?php if (!empty($item['foto_url'])): ?>
                            <div class="form-grupo">
                                <label>Foto</label>
                                <img src="<?= htmlspecialchars($item['foto_url']) ?>" class="vistoria-foto-thumb" alt="Foto da ocorrência" onclick="abrirLightbox(this.src)">
                            </div>
                        <?php endif; ?>

                        <div class="form-grupo">
                            <label>Status *</label>
                            <div class="select-wrapper">
                                <select name="status[<?= $item['id_ocorrencia'] ?>]" required>
                                    <option value="aberta"    <?= $item['status'] === 'aberta'    ? 'selected' : '' ?>>Aberta</option>
                                    <option value="resolvida" <?= $item['status'] === 'resolvida' ? 'selected' : '' ?>>Resolvida</option>
                                </select>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

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