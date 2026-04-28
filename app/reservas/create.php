<?php
session_start();
include '../../includes/auth/login_verify.php';
include '../../config/db.php';
$titulo_pagina = "Reservas - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";

$id_usuario = $_SESSION['usuario_id'];
$erro = '';
$sucesso = '';

$data_previa = $_GET['data'] ?? '';

$ambientes = $conn->query("SELECT id_ambiente, nome_ambiente FROM ambientes WHERE ativo = 1 ORDER BY nome_ambiente");

$funcionarios = $conn->query("SELECT id_funcionario, nome_completo FROM funcionarios WHERE ativo = 1 ORDER BY nome_completo");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $nome = trim($_POST['nome_reserva']);
    $tel = trim($_POST['telefone_reserva']);
    $data = parse_data($_POST['data_reserva']);
    $inicio = $_POST['hora_inicio'];
    $fim = $_POST['hora_fim'];
    $ambiente = $_POST['id_ambiente'];
    $func = empty($_POST['id_funcionario']) ? null : $_POST['id_funcionario']; // pois funcionario nao precisa ser definido
    $valor_cobrado = floatval($_POST['valor_cobrado']);
    $valor_pago = floatval($_POST['valor_pago']);
    $obs = trim($_POST['observacoes']);

    if ($fim <= $inicio) {
        $erro = "A hora de término deve ser maior que a hora de início.";
    }

    if (!$erro && $data < date('Y-m-d')) {
        $erro = "Não é possível criar reservas no passado.";
    }

    if (!$erro) {
        $stmtBl = $conn->prepare("
        SELECT id_bloqueio FROM bloqueios 
        WHERE ativo = 1 
          AND data_inicio <= ? 
          AND data_fim >= ?
    ");
        $stmtBl->bind_param("ss", $data, $data);
        $stmtBl->execute();
        $stmtBl->store_result();

        if ($stmtBl->num_rows > 0) {
            $erro = "Esta data está bloqueada e não pode receber reservas.";
        }
        $stmtBl->close();
    }

    if (!$erro && $valor_pago > $valor_cobrado) {
        $erro = "O valor pago não pode ser maior que o valor cobrado.";
    }

    // verifica conflito de horários
    if (!$erro) {
        $sql = "
            SELECT * FROM reservas 
            WHERE id_ambiente = ? 
            AND data_reserva = ?
            AND (
                (? BETWEEN hora_inicio AND hora_fim)
                OR (? BETWEEN hora_inicio AND hora_fim)
                OR (hora_inicio BETWEEN ? AND ?)
            )
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $ambiente, $data, $inicio, $fim, $inicio, $fim);
        $stmt->execute();
        $conf = $stmt->get_result();

        if ($conf->num_rows > 0) {
            $erro = "Este ambiente já está reservado nesse período!";
        }
    }

    if (!$erro) {
        $stmt = $conn->prepare("
            INSERT INTO reservas 
            (id_usuario, nome_reserva, telefone_reserva, data_reserva, hora_inicio, hora_fim, id_ambiente, id_funcionario, valor_cobrado, valor_pago, observacoes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "isssssisdds",
            $id_usuario,
            $nome,
            $tel,
            $data,
            $inicio,
            $fim,
            $ambiente,
            $func,
            $valor_cobrado,
            $valor_pago,
            $obs
        );

        if ($stmt->execute()) {
            registrar_log($conn, $id_usuario, 'criar', 'reserva', $conn->insert_id);
            header("Location: index.php?sucesso=1");
            exit;
        } else {
            $erro = "Erro ao cadastrar a reserva. Tente novamente.";
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
            <h1>Nova Reserva</h1>
        </header>

        <div class="header-acoes">
            <a href="index.php" class="btn-voltar">
                <i class="fa-solid fa-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="cadastro-area">

            <?php if (!empty($erro)): ?>
                <div class="alerta erro"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro">
                <?= csrf_field() ?>

                <div class="form-grupo">
                    <label>Nome *</label>
                    <input type="text" name="nome_reserva" required>
                </div>

                <div class="form-grupo">
                    <label>Telefone *</label>
                    <input type="text" name="telefone_reserva" data-mask='(00) 00000 - 0000' required>
                </div>

                <div class="form-grupo">
                    <label>Data da reserva</label>
                    <input type="text" class="input-data" name="data_reserva" placeholder="DD/MM/AAAA" tabindex="-1" readonly required
                        value="<?= fmt_data($data_previa) ?>">
                </div>

                <div class="form-grupo">
                    <label>Hora de início *</label>
                    <input type="time" name="hora_inicio" required>
                </div>

                <div class="form-grupo">
                    <label>Hora de término *</label>
                    <input type="time" name="hora_fim" required>
                </div>

                <div class="form-grupo">
                    <label>Ambiente *</label>
                    <div class="select-wrapper">
                        <select name="id_ambiente" required>
                            <option value="">Selecione</option>
                            <?php while ($a = $ambientes->fetch_assoc()): ?>
                                <option value="<?= $a['id_ambiente'] ?>">
                                    <?= $a['nome_ambiente'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-grupo">
                    <label>Funcionário</label>
                    <div class="select-wrapper">
                        <select name="id_funcionario">
                            <option value="">Não definido</option>
                            <?php while ($f = $funcionarios->fetch_assoc()): ?>
                                <option value="<?= $f['id_funcionario'] ?>">
                                    <?= $f['nome_completo'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-grupo">
                    <label>Valor cobrado (R$) *</label>
                    <input type="number" step="0.01" name="valor_cobrado" min="0" id="valor_cobrado" required
                        oninput="calcularFalta()">
                </div>

                <div class="form-grupo">
                    <label>Valor pago (R$)</label>
                    <input type="number" step="0.01" name="valor_pago" min="0" id="valor_pago"
                        oninput="calcularFalta()">
                </div>

                <div class="form-grupo">
                    <label>Falta pagar (R$)</label>
                    <input type="text" id="valor_falta" tabindex="-1" readonly>
                </div>

                <div class="form-grupo">
                    <label>Observações</label>
                    <textarea name="observacoes"></textarea>
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

<?php include "../../includes/layout/footer.php"; ?>