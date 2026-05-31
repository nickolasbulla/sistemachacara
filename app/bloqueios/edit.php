<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/init.php';

$titulo_pagina = "Bloqueios - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";

$id = $_GET['id'] ?? null;
$erro = '';

if (!$id) {
    header('Location: index.php');
    exit;
}

// busca o bloqueio
$stmt = $conn->prepare("SELECT * FROM bloqueios WHERE id_bloqueio = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$bloqueio = $result->fetch_assoc();
$stmt->close();

if (!$bloqueio) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $data_inicio = parse_data($_POST['data_inicio']);
    $data_fim    = parse_data($_POST['data_fim']);
    $motivo      = trim($_POST['motivo']);
    $ativo       = isset($_POST['ativo']) ? 1 : 0;

    if (empty($data_inicio) || empty($data_fim) || empty($motivo)) {
        $erro = "Preencha todos os campos obrigatórios.";
    } elseif ($data_inicio < date('Y-m-d')) {
        $erro = "A data de início não pode ser no passado.";
    } elseif ($data_fim < $data_inicio) {
        $erro = "A data de fim não pode ser anterior à data de início.";
    } else {

        $locked = $conn->query("SELECT GET_LOCK('bloqueios_write', 5)")->fetch_row()[0];
        if (!$locked) {
            $erro = "Não foi possível processar agora. Tente novamente.";
        } else {
            $stmtChk = $conn->prepare("
                SELECT id_reserva, nome_reserva, data_reserva
                FROM reservas
                WHERE data_reserva BETWEEN ? AND ?
                LIMIT 1
            ");
            $stmtChk->bind_param("ss", $data_inicio, $data_fim);
            $stmtChk->execute();
            $reservaConflito = $stmtChk->get_result()->fetch_assoc();
            $stmtChk->close();

            if ($reservaConflito) {
                $erro = "Não é possível bloquear este período pois já existe uma reserva em "
                    . date('d/m/Y', strtotime($reservaConflito['data_reserva']))
                    . " (" . htmlspecialchars($reservaConflito['nome_reserva']) . ").";
            } else {
                $stmt = $conn->prepare("UPDATE bloqueios SET data_inicio = ?, data_fim = ?, motivo = ?, ativo = ? WHERE id_bloqueio = ?");
                $stmt->bind_param("sssii", $data_inicio, $data_fim, $motivo, $ativo, $id);

                if ($stmt->execute()) {
                    $conn->query("SELECT RELEASE_LOCK('bloqueios_write')");
                    registrar_log($conn, $_SESSION['usuario_id'], 'editar', 'bloqueio', (int) $id, date('d/m/Y', strtotime($data_inicio)) . " até " . date('d/m/Y', strtotime($data_fim)) . " | $motivo");
                    header("Location: index.php?editado=1");
                    exit;
                } else {
                    $erro = "Erro ao atualizar o bloqueio. Tente novamente.";
                }
            }

            $conn->query("SELECT RELEASE_LOCK('bloqueios_write')");
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
            <h1>Editar Bloqueio</h1>
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
                    <label>Data de início: *</label>
                    <input type="text" class="input-data" name="data_inicio" placeholder="DD/MM/AAAA" required
                           value="<?= htmlspecialchars($_POST['data_inicio'] ?? fmt_data($bloqueio['data_inicio'])) ?>">
                </div>

                <div class="form-grupo">
                    <label>Data de fim: *</label>
                    <input type="text" class="input-data" name="data_fim" placeholder="DD/MM/AAAA" required
                           value="<?= htmlspecialchars($_POST['data_fim'] ?? fmt_data($bloqueio['data_fim'])) ?>">
                </div>

                <div class="form-grupo">
                    <label>Motivo: *</label>
                    <textarea name="motivo" rows="4" required><?= htmlspecialchars($_POST['motivo'] ?? $bloqueio['motivo']) ?></textarea>
                </div>

                <div class="form-grupo checkbox">
                    <label class="checkbox-container">
                        <input type="checkbox" name="ativo"
                               <?= (isset($_POST['ativo']) && $_SERVER['REQUEST_METHOD'] === 'POST' ? true : (bool)($bloqueio['ativo'] ?? 1)) ? 'checked' : '' ?>>
                        <span class="checkmark"></span>
                        Bloqueio ativo
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