<?php
session_start();
include '../includes/login_verify.php';
include '../includes/db.php';
$titulo_pagina = "Reservas - Chácara Portal";
$css_pagina = ["../assets/css/painel.css", "../assets/css/crud.css"];
include "../includes/header.php";

// verifica se recebeu id
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

//delete
if (isset($_GET['delete_id'])) {
    $id = (int) $_GET['delete_id'];

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM reservas WHERE id_reserva = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: index.php?deletado=1");
    exit;
}

$id = intval($_GET['id']);
$erro = '';
$sucesso = '';

// busca a reserva
$sql = "SELECT * FROM reservas WHERE id_reserva = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$reserva = $stmt->get_result()->fetch_assoc();

if (!$reserva) {
    echo "<div class='alerta erro'>Reserva não encontrada!</div>";
    include '../includes/footer.php';
    exit;
}

// busca ambientes e funcionários
$ambientes = $conn->query("SELECT id_ambiente, nome_ambiente FROM ambientes WHERE ativo = 1 ORDER BY nome_ambiente");
$funcionarios = $conn->query("SELECT id_funcionario, nome_completo FROM funcionarios WHERE ativo = 1 ORDER BY nome_completo");

// PROCESSAR UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome      = trim($_POST['nome_reserva']);
    $tel       = trim($_POST['telefone_reserva']);
    $data      = $_POST['data_reserva'];
    $inicio    = $_POST['hora_inicio'];
    $fim       = $_POST['hora_fim'];
    $ambiente  = $_POST['id_ambiente'];
    $func      = $_POST['id_funcionario'] ?: null;
    $pago      = isset($_POST['pago']) ? 1 : 0;
    $obs       = trim($_POST['observacoes']);

    // VALIDAÇÕES

    if ($fim <= $inicio) {
        $erro = "A hora de término deve ser maior que a hora de início.";
    }

    if (!$erro && $data < date('Y-m-d')) {
        $erro = "Não é possível editar para uma data no passado.";
    }

    // valida conflito de horário (exceto com a própria reserva)
    if (!$erro) {
        $sql_conf = "
            SELECT id_reserva FROM reservas 
            WHERE id_ambiente = ?
            AND data_reserva = ?
            AND id_reserva != ?
            AND (
                (? BETWEEN hora_inicio AND hora_fim)
                OR (? BETWEEN hora_inicio AND hora_fim)
                OR (hora_inicio BETWEEN ? AND ?)
            )
        ";

        $stmt_conf = $conn->prepare($sql_conf);
        $stmt_conf->bind_param("isisiss", 
            $ambiente, $data, $id, 
            $inicio, $fim, $inicio, $fim
        );

        $stmt_conf->execute();
        $conf = $stmt_conf->get_result();

        if ($conf->num_rows > 0) {
            $erro = "Este ambiente já possui reserva nesse período!";
        }
    }

    // se tudo ok atualizar
    if (!$erro) {

        $sql_up = "
            UPDATE reservas SET 
                nome_reserva = ?,
                telefone_reserva = ?,
                data_reserva = ?,
                hora_inicio = ?,
                hora_fim = ?,
                id_ambiente = ?,
                id_funcionario = ?,
                pago = ?,
                observacoes = ?
            WHERE id_reserva = ?
        ";

        $stmt_up = $conn->prepare($sql_up);
        $stmt_up->bind_param("sssssiissi",
            $nome, $tel, $data, $inicio, $fim,
            $ambiente, $func, $pago, $obs, $id
        );

        $stmt_up->execute();

        header("Location: index.php?editado=1");
        exit;
    }
}
?>

<div class="painel-container">

    <?php include '../includes/sidebar.php'; ?>
    <div class="sidebar-overlay"></div>

    <main class="conteudo">

        <div class="painel-header">
            <button class="menu-toggle">☰</button>
            <h1>Editar Reserva</h1>
            <p>Atualize as informações abaixo.</p>
        </div>

        <div class="form-botoes" style="justify-content: space-between; margin-bottom: 20px;">

            <!-- BOTÃO EXCLUIR -->
            <a href="#" 
            class="btn btn-excluir btnpopup" 
            data-id="<?= $reserva['id_reserva'] ?>">
            🗑️ Excluir Reserva
            </a>

            <!-- BOTÃO CRIAR OUTRA RESERVA NO MESMO DIA -->
            <a href="./create.php?data=<?= $reserva['data_reserva'] ?>" 
            class="btn btn-novo">
            ➕ Nova Reserva neste dia
            </a>

        </div>

        <div class="cadastro-area">

            <a href="./index.php" class="btn-voltar">← Voltar</a>

            <?php if (!empty($erro)) : ?>
                <div class="alerta erro"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro">

                <div class="form-grupo">
                    <label>Nome de quem reservou</label>
                    <input type="text" name="nome_reserva" value="<?= htmlspecialchars($reserva['nome_reserva']) ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Telefone</label>
                    <input type="text" name="telefone_reserva" value="<?= htmlspecialchars($reserva['telefone_reserva']) ?>">
                </div>

                <div class="form-grupo">
                    <label>Data da reserva</label>
                    <input type="date" name="data_reserva" value="<?= $reserva['data_reserva'] ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Hora de início</label>
                    <input type="time" name="hora_inicio" value="<?= $reserva['hora_inicio'] ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Hora de término</label>
                    <input type="time" name="hora_fim" value="<?= $reserva['hora_fim'] ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Ambiente</label>
                    <select name="id_ambiente" required>
                        <option value="">Selecione</option>
                        <?php while ($a = $ambientes->fetch_assoc()): ?>
                            <option value="<?= $a['id_ambiente'] ?>"
                                <?= $a['id_ambiente'] == $reserva['id_ambiente'] ? 'selected' : '' ?>>
                                <?= $a['nome_ambiente'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-grupo">
                    <label>Funcionário de limpeza</label>
                    <select name="id_funcionario">
                        <option value="">Não definido</option>
                        <?php while ($f = $funcionarios->fetch_assoc()): ?>
                            <option value="<?= $f['id_funcionario'] ?>"
                                <?= $f['id_funcionario'] == $reserva['id_funcionario'] ? 'selected' : '' ?>>
                                <?= $f['nome_completo'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-grupo checkbox">
                    <label>
                        <input type="checkbox" name="pago" <?= $reserva['pago'] ? 'checked' : '' ?>> Pago
                    </label>
                </div>

                <div class="form-grupo">
                    <label>Observações</label>
                    <textarea name="observacoes"><?= htmlspecialchars($reserva['observacoes']) ?></textarea>
                </div>

                <div class="form-botoes">
                    <button type="submit" class="btn btn-salvar">💾 Salvar</button>
                    <a href="./index.php" class="btn btn-cancelar">Cancelar</a>
                </div>

            </form>
        </div>
    </main>
</div>

<!-- pop up delete -->
<div id="deleteModal" class="popup-modal">
    <div class="popup-box">
        <h2>Excluir reserva?</h2>
        <p>Essa ação não pode ser desfeita.</p>

        <div class="popup-buttons">
            <button id="cancelDelete" class="btn btn-cancelar">Cancelar</button>
            <a href="#" id="confirmDelete" class="btn btn-confirmar">Sim, excluir</a>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>