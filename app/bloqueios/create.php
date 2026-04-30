<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/db.php';

$titulo_pagina = "Bloqueios - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";

$erro = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    $data_inicio = parse_data($_POST["data_inicio"]);
    $data_fim    = parse_data($_POST["data_fim"]);
    $motivo = trim($_POST["motivo"]);
    $id_usuario = $_SESSION['usuario_id'];

    if (empty($data_inicio) || empty($data_fim) || empty($motivo)) {
        $erro = "Preencha todos os campos obrigatórios.";
    } elseif ($data_fim < $data_inicio) {
        $erro = "A data inicial não pode ser maior que a data final.";
    } elseif ($data_inicio < date('Y-m-d')) {
        $erro = "A data de início não pode ser no passado.";
    } else {

        $stmtChk = $conn->prepare("
        SELECT id_reserva, nome_reserva, data_reserva 
        FROM reservas 
        WHERE data_reserva BETWEEN ? AND ?
        LIMIT 1
    ");
        $stmtChk->bind_param("ss", $data_inicio, $data_fim);
        $stmtChk->execute();
        $resChk = $stmtChk->get_result();
        $reservaConflito = $resChk->fetch_assoc();
        $stmtChk->close();

        if ($reservaConflito) {
            $erro = "Não é possível bloquear este período pois já existe uma reserva em "
                . date('d/m/Y', strtotime($reservaConflito['data_reserva']))
                . " (" . htmlspecialchars($reservaConflito['nome_reserva']) . ").";
        } else {
            $stmt = $conn->prepare("INSERT INTO bloqueios (data_inicio, data_fim, motivo, id_usuario) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $data_inicio, $data_fim, $motivo, $id_usuario);

            if ($stmt->execute()) {
                registrar_log($conn, $_SESSION['usuario_id'], 'criar', 'bloqueio', $conn->insert_id, date('d/m/Y', strtotime($data_inicio)) . " até " . date('d/m/Y', strtotime($data_fim)) . " | $motivo");
                header("Location: index.php?sucesso=1");
                exit;
            } else {
                $erro = "Erro ao cadastrar o bloqueio. Tente novamente.";
            }
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
            <h1>Novo Bloqueio de Data</h1>
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
                        value="<?= htmlspecialchars($_POST['data_inicio'] ?? '') ?>">
                </div>

                <div class="form-grupo">
                    <label>Data de fim: *</label>
                    <input type="text" class="input-data" name="data_fim" placeholder="DD/MM/AAAA" required
                        value="<?= htmlspecialchars($_POST['data_fim'] ?? '') ?>">
                </div>

                <div class="form-grupo">
                    <label>Motivo: *</label>
                    <textarea name="motivo" rows="4" required
                        placeholder="Ex: Manutenção, evento particular..."><?= htmlspecialchars($_POST['motivo'] ?? '') ?></textarea>
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