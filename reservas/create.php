<?php
session_start();
include '../includes/login_verify.php';
include '../includes/db.php';
$titulo_pagina = "Reservas - Chácara Portal";
$css_pagina = ["../assets/css/painel.css", "../assets/css/crud.css"];
include "../includes/header.php";

$id_usuario = $_SESSION['usuario_id'];
$erro = '';
$sucesso = '';

$data_previa = isset($_GET['data']) ? $_GET['data'] : '';

// busca ambientes
$ambientes = $conn->query("SELECT id_ambiente, nome_ambiente FROM ambientes WHERE ativo = 1 ORDER BY nome_ambiente");

// busca funcionários
$funcionarios = $conn->query("SELECT id_funcionario, nome_completo FROM funcionarios WHERE ativo = 1 ORDER BY nome_completo");

// processar o form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome      = trim($_POST['nome_reserva']);
    $tel       = trim($_POST['telefone_reserva']);
    $data      = $_POST['data_reserva'];
    $inicio    = $_POST['hora_inicio'];
    $fim       = $_POST['hora_fim'];
    $ambiente  = $_POST['id_ambiente'];
    $func      = empty($_POST['id_funcionario']) ? null : $_POST['id_funcionario']; // pois funcionario nao precisa ser definido
    $valor_cobrado = floatval($_POST['valor_cobrado']);
    $valor_pago    = floatval($_POST['valor_pago']);
    $obs       = trim($_POST['observacoes']);

    // impede hora inválida
    if ($fim <= $inicio) {
        $erro = "A hora de término deve ser maior que a hora de início.";
    }

    // impede criar no passado
    if (!$erro && $data < date('Y-m-d')) {
        $erro = "Não é possível criar reservas no passado.";
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

    // se passou por tudo, insere
    if (!$erro) {
        $stmt = $conn->prepare("
            INSERT INTO reservas 
            (id_usuario, nome_reserva, telefone_reserva, data_reserva, hora_inicio, hora_fim, id_ambiente, id_funcionario, valor_cobrado, valor_pago, observacoes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("isssssiiids", 
        $id_usuario,$nome, $tel, $data, $inicio, $fim, $ambiente, $func, $valor_cobrado, $valor_pago, $obs
        );

        $stmt->execute();

        header("Location: index.php?sucesso=1");
        exit;
    }
}
?>

<div class="painel-container">

    <?php include '../includes/sidebar.php'; ?>
    <div class="sidebar-overlay"></div>

    <main class="conteudo">
        <header class="painel-header">
            <button class="menu-toggle">☰</button>
            <h1>Nova Reserva</h1>
            <p>Preenche os dados abaixo para registrar uma nova reserva.</p>
        </header>

        <a href="./index.php" class="btn-voltar">← Voltar</a>

        <div class="cadastro-area">

            <?php if (!empty($erro)) : ?>
                <div class="alerta erro"><?=$erro ?></div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro">

                <div class="form-grupo">
                    <label>Nome *</label>
                    <input type="text" name="nome_reserva" required>
                </div>

                <div class="form-grupo">
                    <label>Telefone *</label>
                    <input type="text" name="telefone_reserva" data-mask='(00) 00000 - 0000' required>
                </div>

                <div class="form-grupo">
                    <label>Data da reserva *</label>
                    <input type="date" name="data_reserva" value="<?= $data_previa ?>" required>
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
                    <select name="id_ambiente" required>
                        <option value="">Selecione</option>
                        <?php while ($a = $ambientes->fetch_assoc()): ?>
                            <option value="<?= $a['id_ambiente'] ?>"><?= $a['nome_ambiente'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-grupo">
                    <label>Funcionário</label>
                    <select name="id_funcionario">
                        <option value="">Não definido</option>
                        <?php while ($f = $funcionarios->fetch_assoc()): ?>
                            <option value="<?= $f['id_funcionario'] ?>"><?= $f['nome_completo'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-grupo">
                    <label>Valor cobrado (R$) *</label>
                    <input type="number" step="0.01" name="valor_cobrado" min="0" id="valor_cobrado" required oninput="calcularFalta()">
                </div>

                <div class="form-grupo">
                    <label>Valor pago (R$)</label>
                    <input type="number" step="0.01" name="valor_pago" min="0" id="valor_pago" oninput="calcularFalta()">
                </div>

                <div class="form-grupo">
                    <label>Falta pagar (R$)</label>
                    <input type="text" id="valor_falta" readonly>
                </div>

                <div class="form-grupo">
                    <label>Observações</label>
                    <textarea name="observacoes"></textarea>
                </div>

                <div class="form-botoes">
                    <button type="submit" class="btn btn-salvar">💾 Salvar</button>
                    <a href="./index.php" class="btn btn-cancelar">Cancelar</a>
                </div>

            </form>
        </div>
    </main>
</div>

<?php include "../includes/footer.php"; ?>