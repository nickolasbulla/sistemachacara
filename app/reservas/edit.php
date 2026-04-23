<?php
session_start();
include '../../includes/auth/login_verify.php';
include '../../config/db.php';
$titulo_pagina = "Reservas - Chácara Portal";

include "../../includes/layout/header.php";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

// delete
if (isset($_GET['delete_id'])) {
    $id = (int) $_GET['delete_id'];

    if ($id > 0) {
        $info = $conn->prepare("SELECT nome_reserva, data_reserva, hora_inicio, hora_fim FROM reservas WHERE id_reserva = ?");
        $info->bind_param("i", $id);
        $info->execute();
        $row = $info->get_result()->fetch_assoc();
        $detalhes = $row ? "nome: {$row['nome_reserva']} | data: {$row['data_reserva']} | horário: {$row['hora_inicio']}-{$row['hora_fim']}" : null;

        try {
            $stmt = $conn->prepare("DELETE FROM reservas WHERE id_reserva = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            registrar_log($conn, $_SESSION['usuario_id'], 'excluir', 'reserva', $id, $detalhes);
        } catch (mysqli_sql_exception $e) {
            header("Location: index.php?erro_relacionado=1");
            exit;
        }
    }

    header("Location: index.php?deletado=1");
    exit;
}

$id = intval($_GET['id']);
$erro = '';
$erro_vistoria = '';
$sucesso_vistoria = '';

$sql = "SELECT * FROM reservas WHERE id_reserva = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$reserva = $stmt->get_result()->fetch_assoc();

if (!$reserva) {
    echo "<div class='alerta erro'>Reserva não encontrada!</div>";
    include '../../includes/layout/footer.php';
    exit;
}

$ambientes = $conn->query("SELECT id_ambiente, nome_ambiente FROM ambientes WHERE ativo = 1 ORDER BY nome_ambiente");
$funcionarios = $conn->query("SELECT id_funcionario, nome_completo FROM funcionarios WHERE ativo = 1 ORDER BY nome_completo");

$is_passada = $reserva['data_reserva'] < date('Y-m-d');

// ─── POST: salvar edição da reserva ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_reserva'])) {

    $nome          = trim($_POST['nome_reserva']);
    $tel           = trim($_POST['telefone_reserva']);
    $data          = $_POST['data_reserva'];
    $inicio        = $_POST['hora_inicio'];
    $fim           = $_POST['hora_fim'];
    $ambiente      = $_POST['id_ambiente'];
    $func          = $_POST['id_funcionario'] ?: null;
    $valor_cobrado = floatval($_POST['valor_cobrado']);
    $valor_pago    = floatval($_POST['valor_pago']);
    $obs           = trim($_POST['observacoes']);

    if ($fim <= $inicio) {
        $erro = "A hora de término deve ser maior que a hora de início.";
    }

    if (!$erro && $data < date('Y-m-d')) {
        $erro = "Não é possível editar para uma data no passado.";
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

    if ($valor_cobrado < 0 || $valor_pago < 0) {
        $erro = "Valores não podem ser negativos.";
    }

    if (!$erro && $valor_pago > $valor_cobrado) {
        $erro = "O valor pago não pode ser maior que o valor cobrado.";
    }

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
        $stmt_conf->bind_param("isisiss", $ambiente, $data, $id, $inicio, $fim, $inicio, $fim);
        $stmt_conf->execute();
        if ($stmt_conf->get_result()->num_rows > 0) {
            $erro = "Este ambiente já possui reserva nesse período!";
        }
    }

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
                valor_cobrado = ?,
                valor_pago = ?,
                observacoes = ?
            WHERE id_reserva = ?
        ";
        $stmt_up = $conn->prepare($sql_up);
        $stmt_up->bind_param("sssssiiddsi", $nome, $tel, $data, $inicio, $fim, $ambiente, $func, $valor_cobrado, $valor_pago, $obs, $id);
        if ($stmt_up->execute()) {
            registrar_log($conn, $_SESSION['usuario_id'], 'editar', 'reserva', $id);
            header("Location: index.php?editado=1");
            exit;
        } else {
            $erro = "Erro ao atualizar a reserva. Tente novamente.";
        }
    }
}

// ─── POST: registrar vistoria ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_vistoria'])) {

    $id_usuario = $_SESSION['usuario_id'];
    $itens_form = $_POST['itens'] ?? [];
    $descricoes = $_POST['descricao'] ?? [];

    // verifica se já existe vistoria pra essa reserva
    $chk = $conn->prepare("SELECT id_vistoria_resultado FROM vistoria_resultados WHERE id_reserva = ? LIMIT 1");
    $chk->bind_param("i", $id);
    $chk->execute();
    $chk->store_result();

    if ($chk->num_rows > 0) {
        $erro_vistoria = "Já existe uma vistoria registrada para esta reserva.";
    } else {
        // busca todos os itens ativos
        $itens_ativos = [];
        $res = $conn->query("SELECT id_item_vistoria FROM itens_vistoria WHERE ativo = 1");
        while ($row = $res->fetch_assoc()) {
            $itens_ativos[] = $row['id_item_vistoria'];
        }

        $conn->begin_transaction();
        try {
            foreach ($itens_ativos as $id_item) {
                $conforme           = isset($itens_form[$id_item]) ? 1 : 0;
                $descricao_problema = $conforme ? null : ($descricoes[$id_item] ?? null);

                $stmt_vi = $conn->prepare("
                    INSERT INTO vistoria_resultados (id_reserva, id_item_vistoria, id_usuario, conforme, descricao_problema)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt_vi->bind_param("iiiis", $id, $id_item, $id_usuario, $conforme, $descricao_problema);
                $stmt_vi->execute();
                $id_vistoria_resultado = $conn->insert_id;

                // abre ocorrência se não conforme
                if (!$conforme && !empty($descricao_problema)) {
                    $stmt_oc = $conn->prepare("
                        INSERT INTO ocorrencias (id_vistoria_resultado, id_reserva, id_item_vistoria, id_usuario, descricao)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt_oc->bind_param("iiiis", $id_vistoria_resultado, $id, $id_item, $id_usuario, $descricao_problema);
                    $stmt_oc->execute();
                }
            }

            $conn->commit();
            registrar_log($conn, $id_usuario, 'registrar_vistoria', 'reserva', $id);
            $sucesso_vistoria = "Vistoria registrada com sucesso!";

        } catch (Exception $e) {
            $conn->rollback();
            $erro_vistoria = "Erro ao registrar a vistoria. Tente novamente.";
        }
    }
}

// ─── Dados da vistoria (para exibir se já registrada) ────────────────────────
$vistoria_registrada = null;
if ($is_passada) {
    $stmt_vr = $conn->prepare("
        SELECT vr.*, iv.nome_item
        FROM vistoria_resultados vr
        INNER JOIN itens_vistoria iv ON iv.id_item_vistoria = vr.id_item_vistoria
        WHERE vr.id_reserva = ?
        ORDER BY iv.nome_item ASC
    ");
    $stmt_vr->bind_param("i", $id);
    $stmt_vr->execute();
    $result_vr = $stmt_vr->get_result();
    if ($result_vr->num_rows > 0) {
        $vistoria_registrada = [];
        while ($row = $result_vr->fetch_assoc()) {
            $vistoria_registrada[] = $row;
        }
    }
}

// Itens ativos para o form de vistoria
$itens_vistoria_lista = [];
if ($is_passada && !$vistoria_registrada) {
    $res_itens = $conn->query("SELECT * FROM itens_vistoria WHERE ativo = 1 ORDER BY nome_item ASC");
    while ($row = $res_itens->fetch_assoc()) {
        $itens_vistoria_lista[] = $row;
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
        </header>

        <div class="form-botoes">
            <a href="index.php" class="btn-voltar">
                <i class="fa-solid fa-arrow-left"></i>
                Voltar
            </a>
            <a href="#" class="btn btn-excluir btnpopup" data-id="<?= $reserva['id_reserva'] ?>">
                <i class="fa-solid fa-trash"></i>
                Excluir Reserva
            </a>
        </div>

        <div class="cadastro-area">

            <?php if (!empty($erro)): ?>
                <div class="alerta erro"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro">

                <input type="hidden" name="salvar_reserva" value="1">

                <div class="form-grupo">
                    <label>Nome *</label>
                    <input type="text" name="nome_reserva" value="<?= htmlspecialchars($reserva['nome_reserva']) ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Telefone *</label>
                    <input type="text" name="telefone_reserva" data-mask='(00) 00000 - 0000' value="<?= htmlspecialchars($reserva['telefone_reserva']) ?>">
                </div>

                <div class="form-grupo">
                    <label>Data da reserva</label>
                    <input type="date" name="data_reserva" value="<?= $reserva['data_reserva'] ?>" tabindex="-1" readonly required>
                </div>

                <div class="form-grupo">
                    <label>Hora de início *</label>
                    <input type="time" name="hora_inicio" value="<?= $reserva['hora_inicio'] ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Hora de término *</label>
                    <input type="time" name="hora_fim" value="<?= $reserva['hora_fim'] ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Ambiente *</label>
                    <div class="select-wrapper">
                        <select name="id_ambiente" required>
                            <option value="">Selecione</option>
                            <?php while ($a = $ambientes->fetch_assoc()): ?>
                                <option value="<?= $a['id_ambiente'] ?>" <?= $a['id_ambiente'] == $reserva['id_ambiente'] ? 'selected' : '' ?>>
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
                                <option value="<?= $f['id_funcionario'] ?>" <?= $f['id_funcionario'] == $reserva['id_funcionario'] ? 'selected' : '' ?>>
                                    <?= $f['nome_completo'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-grupo">
                    <label>Valor cobrado (R$) *</label>
                    <input type="number" min="0" step="0.01" name="valor_cobrado" id="valor_cobrado" value="<?= number_format($reserva['valor_cobrado'], 2, '.', '') ?>" oninput="calcularFalta()" required>
                </div>

                <div class="form-grupo">
                    <label>Valor pago (R$)</label>
                    <input type="number" min="0" step="0.01" name="valor_pago" id="valor_pago" value="<?= number_format($reserva['valor_pago'], 2, '.', '') ?>" oninput="calcularFalta()">
                </div>

                <div class="form-grupo">
                    <label>Falta pagar (R$)</label>
                    <input type="text" id="valor_falta" tabindex="-1" readonly>
                </div>

                <div class="form-grupo">
                    <label>Observações</label>
                    <textarea name="observacoes"><?= htmlspecialchars($reserva['observacoes']) ?></textarea>
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

        <?php if ($is_passada): ?>
        <!-- ─── Bloco de Vistoria ─────────────────────────────────────── -->
        <div class="cadastro-area">

            <h3 style="margin: 0 0 20px; font-size: 21px; color: #333; text-align: center;">
                <i class="fa-solid fa-clipboard-list" style="color: #667eea; margin-right: 8px;"></i>
                Vistoria de Saída
            </h3>

            <?php if (!empty($erro_vistoria)): ?>
                <div class="alerta erro"><?= htmlspecialchars($erro_vistoria) ?></div>
            <?php endif; ?>

            <?php if (!empty($sucesso_vistoria)): ?>
                <div class="alerta sucesso"><?= htmlspecialchars($sucesso_vistoria) ?></div>
            <?php endif; ?>

            <?php if ($vistoria_registrada): ?>
                <!-- Vistoria já feita: exibe somente leitura -->
                <div class="tabela-wrapper">
                    <table class="tabela-crud">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Situação</th>
                                <th>Observação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vistoria_registrada as $vr): ?>
                                <tr>
                                    <td data-label="Item"><?= htmlspecialchars($vr['nome_item']) ?></td>
                                    <td data-label="Situação">
                                        <?= $vr['conforme']
                                            ? '<i class="fa-solid fa-check"></i>'
                                            : '<i class="fa-solid fa-xmark"></i>' ?>
                                    </td>
                                    <td data-label="Observação">
                                        <?= (!$vr['conforme'] && !empty($vr['descricao_problema']))
                                            ? htmlspecialchars($vr['descricao_problema'])
                                            : '—' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif (empty($itens_vistoria_lista)): ?>
                <p>Nenhum item de vistoria cadastrado.</p>

            <?php else: ?>
                <!-- Form de vistoria -->
                <form method="POST" class="form-cadastro">
                    <input type="hidden" name="registrar_vistoria" value="1">

                    <?php foreach ($itens_vistoria_lista as $item): ?>
                        <div class="form-grupo checkbox" id="vi-wrap-<?= $item['id_item_vistoria'] ?>">
                            <label>
                                <input
                                    type="checkbox"
                                    name="itens[<?= $item['id_item_vistoria'] ?>]"
                                    value="1"
                                    checked
                                    onchange="toggleProblema(<?= $item['id_item_vistoria'] ?>, this)"
                                >
                                <?= htmlspecialchars($item['nome_item']) ?>
                            </label>
                            <div id="vi-problema-<?= $item['id_item_vistoria'] ?>" style="display:none; margin-top: 8px;">
                                <textarea
                                    name="descricao[<?= $item['id_item_vistoria'] ?>]"
                                    rows="2"
                                    placeholder="Descreva o problema encontrado..."
                                ></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="form-botoes">
                        <button type="submit" class="btn btn-salvar">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Registrar Vistoria
                        </button>
                    </div>
                </form>

                <script>
                function toggleProblema(id, checkbox) {
                    var wrap = document.getElementById('vi-problema-' + id);
                    if (!checkbox.checked) {
                        wrap.style.display = 'block';
                    } else {
                        wrap.style.display = 'none';
                        wrap.querySelector('textarea').value = '';
                    }
                }
                </script>
            <?php endif; ?>

        </div>
        <?php endif; ?>

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

<?php include "../../includes/layout/footer.php"; ?>