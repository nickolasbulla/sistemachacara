<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/db.php';

$titulo_pagina = "Ocorrências - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";

$id   = $_GET['id'] ?? null;
$erro = '';

if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT
        o.*,
        r.nome_reserva,
        r.data_reserva,
        r.telefone_reserva,
        iv.nome_item,
        iv.descricao AS descricao_item,
        u.nome_completo AS usuario_nome
    FROM ocorrencias o
    INNER JOIN reservas r ON r.id_reserva = o.id_reserva
    INNER JOIN itens_vistoria iv ON iv.id_item_vistoria = o.id_item_vistoria
    INNER JOIN usuarios u ON u.id_usuario = o.id_usuario
    WHERE o.id_ocorrencia = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$ocorrencia = $stmt->get_result()->fetch_assoc();

if (!$ocorrencia) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = $_POST['descricao'];
    $status    = $_POST['status'];

    $upd = $conn->prepare("UPDATE ocorrencias SET descricao = ?, status = ? WHERE id_ocorrencia = ?");
    $upd->bind_param("ssi", $descricao, $status, $id);

    if ($upd->execute()) {
        header("Location: index.php?sucesso=1");
        exit;
    } else {
        $erro = "Erro ao atualizar a ocorrência.";
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

            <!-- Informações somente leitura -->
            <div class="form-cadastro">
                <div class="form-grupo">
                    <label>Reserva:</label>
                    <input type="text" value="<?= htmlspecialchars($ocorrencia['nome_reserva']) ?>" disabled>
                </div>

                <div class="form-grupo">
                    <label>Data da reserva:</label>
                    <input type="text" value="<?= date('d/m/Y', strtotime($ocorrencia['data_reserva'])) ?>" disabled>
                </div>

                <div class="form-grupo">
                    <label>Telefone:</label>
                    <input type="text" value="<?= htmlspecialchars($ocorrencia['telefone_reserva'] ?? '-') ?>" disabled>
                </div>

                <div class="form-grupo">
                    <label>Item da vistoria:</label>
                    <input type="text" value="<?= htmlspecialchars($ocorrencia['nome_item']) ?>" disabled>
                </div>

                <div class="form-grupo">
                    <label>Registrado por:</label>
                    <input type="text" value="<?= htmlspecialchars($ocorrencia['usuario_nome']) ?>" disabled>
                </div>

                <div class="form-grupo">
                    <label>Data e hora do registro:</label>
                    <input type="text" value="<?= date('d/m/Y H:i', strtotime($ocorrencia['data_hora_registro'])) ?>" disabled>
                </div>
            </div>

            <!-- Campos editáveis -->
            <form method="POST" class="form-cadastro">
                <div class="form-grupo">
                    <label>Descrição da ocorrência: *</label>
                    <textarea name="descricao" rows="4" required><?= htmlspecialchars($ocorrencia['descricao']) ?></textarea>
                </div>

                <div class="form-grupo">
                    <label>Status: *</label>
                    <div class="select-wrapper">
                        <select name="status" required>
                            <option value="aberta"    <?= $ocorrencia['status'] === 'aberta'    ? 'selected' : '' ?>>Aberta</option>
                            <option value="resolvida" <?= $ocorrencia['status'] === 'resolvida' ? 'selected' : '' ?>>Resolvida</option>
                        </select>
                    </div>
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