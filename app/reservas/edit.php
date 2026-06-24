<?php
session_start();
include '../../includes/auth/login_verify.php';
include '../../config/init.php';
$titulo_pagina = "Reservas - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";
include '../../includes/layout/deletemodal.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

// ─── DELETE ───────────────────────────────────────────────────────────────────
$erro_delete = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    csrf_verify();
    $del_id = (int) $_POST['delete_id'];

    if ($del_id > 0) {
        $chk = $conn->prepare("SELECT id_vistoria_resultado FROM vistoria_resultados WHERE id_reserva = ? LIMIT 1");
        $chk->bind_param("i", $del_id);
        $chk->execute();
        $chk->store_result();
        $tem_vistoria = $chk->num_rows > 0;
        $chk->close();

        if ($tem_vistoria) {
            $erro_delete = "Não é possível excluir: esta reserva possui uma vistoria registrada.";
        } else {
            $info = $conn->prepare("SELECT nome_reserva, data_reserva, hora_inicio, hora_fim FROM reservas WHERE id_reserva = ?");
            $info->bind_param("i", $del_id);
            $info->execute();
            $row = $info->get_result()->fetch_assoc();
            $detalhes = $row ? "nome: {$row['nome_reserva']} | data: {$row['data_reserva']} | horário: {$row['hora_inicio']}-{$row['hora_fim']}" : null;

            $stmt = $conn->prepare("DELETE FROM reservas WHERE id_reserva = ?");
            $stmt->bind_param("i", $del_id);
            $stmt->execute();
            $stmt->close();
            registrar_log($conn, $_SESSION['usuario_id'], 'excluir', 'reserva', $del_id, $detalhes);
            header("Location: index.php?deletado=1");
            exit;
        }
    }
}

$id = intval($_GET['id']);
$erro = $erro_delete;
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

$is_passada      = $reserva['data_reserva'] < date('Y-m-d');
$mostrar_vistoria = $reserva['data_reserva'] <= date('Y-m-d');

// ─── POST: salvar edição da reserva ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_reserva'])) {
    csrf_verify();
    $nome          = trim($_POST['nome_reserva']);
    $tel           = trim($_POST['telefone_reserva']);
    $data          = parse_data($_POST['data_reserva']);
    $inicio        = $_POST['hora_inicio'];
    $fim           = $_POST['hora_fim'];
    $ambiente      = $_POST['id_ambiente'];
    $func          = $_POST['id_funcionario'] ?: null;
    $valor_cobrado = floatval($_POST['valor_cobrado']);
    $valor_pago    = floatval($_POST['valor_pago']);
    $obs           = trim($_POST['observacoes']);

    if (strlen(preg_replace('/\D/', '', $tel)) < 11) {
        $erro = "Telefone inválido.";
    }

    if (!$erro && !$data) {
        $erro = "Data inválida.";
    }

    if (!$erro && $fim <= $inicio) {
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
        $lock_name = 'reserva_' . (int)$ambiente . '_' . $data;
        $locked = $conn->query("SELECT GET_LOCK('{$lock_name}', 5)")->fetch_row()[0];

        if (!$locked) {
            $erro = "Não foi possível processar a reserva agora. Tente novamente.";
        } else {
            $sql_conf = "
                SELECT id_reserva FROM reservas
                WHERE id_ambiente = ?
                AND data_reserva = ?
                AND id_reserva != ?
                AND hora_inicio < ?
                AND hora_fim > ?
            ";
            $stmt_conf = $conn->prepare($sql_conf);
            $stmt_conf->bind_param("isiss", $ambiente, $data, $id, $fim, $inicio);
            $stmt_conf->execute();

            if ($stmt_conf->get_result()->num_rows > 0) {
                $erro = "Este ambiente já possui reserva nesse período!";
            } else {
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
                    $conn->query("SELECT RELEASE_LOCK('{$lock_name}')");
                    registrar_log($conn, $_SESSION['usuario_id'], 'editar', 'reserva', $id, "$nome | " . date('d/m/Y', strtotime($data)) . " | $inicio-$fim");
                    header("Location: index.php?editado=1");
                    exit;
                } else {
                    $erro = "Erro ao atualizar a reserva. Tente novamente.";
                }
            }

            $conn->query("SELECT RELEASE_LOCK('{$lock_name}')");
        }
    }
}

// ─── POST: salvar dados financeiros (reservas passadas) ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_financeiro'])) {
    csrf_verify();
    $valor_cobrado = floatval($_POST['valor_cobrado']);
    $valor_pago    = floatval($_POST['valor_pago']);
    $obs           = trim($_POST['observacoes']);

    if ($valor_cobrado < 0 || $valor_pago < 0) {
        $erro = "Valores não podem ser negativos.";
    } elseif ($valor_pago > $valor_cobrado) {
        $erro = "O valor pago não pode ser maior que o valor cobrado.";
    } else {
        $stmt_fin = $conn->prepare("UPDATE reservas SET valor_cobrado = ?, valor_pago = ?, observacoes = ? WHERE id_reserva = ?");
        $stmt_fin->bind_param("ddsi", $valor_cobrado, $valor_pago, $obs, $id);
        if ($stmt_fin->execute()) {
            registrar_log($conn, $_SESSION['usuario_id'], 'editar', 'reserva', $id, "dados financeiros | cobrado: {$valor_cobrado} | pago: {$valor_pago}");
            header("Location: index.php?editado=1");
            exit;
        } else {
            $erro = "Erro ao atualizar os dados financeiros. Tente novamente.";
        }
    }
    // re-fetch reserva com valores atualizados em caso de erro
    $stmt = $conn->prepare("SELECT * FROM reservas WHERE id_reserva = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $reserva = $stmt->get_result()->fetch_assoc();
}

// ─── POST: registrar vistoria ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_vistoria'])) {
    csrf_verify();
    $id_usuario = $_SESSION['usuario_id'];
    $itens_form = $_POST['itens'] ?? [];
    $descricoes = $_POST['descricao'] ?? [];
    $fotos      = $_FILES['foto'] ?? [];

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

        foreach ($itens_ativos as $id_item) {
            if (!isset($itens_form[$id_item]) && empty(trim($descricoes[$id_item] ?? ''))) {
                $erro_vistoria = "Descreva o problema para todos os itens não conformes.";
                break;
            }
        }

        if (!empty($erro_vistoria)) goto fim_vistoria;

        $cloudinary = require '../../config/cloudinary.php';

        $conn->begin_transaction();
        try {
            $total_itens      = count($itens_ativos);
            $total_ocorrencias = 0;

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
                    $total_ocorrencias++;

                    // upload de foto para o Cloudinary (opcional)
                    if (
                        !empty($fotos['tmp_name'][$id_item]) &&
                        ($fotos['error'][$id_item] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK
                    ) {
                        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                        if (in_array($fotos['type'][$id_item], $tipos_permitidos) && $fotos['size'][$id_item] <= 5 * 1024 * 1024) {
                            $upload = $cloudinary->uploadApi()->upload(
                                $fotos['tmp_name'][$id_item],
                                ['folder' => 'vistorias', 'resource_type' => 'image']
                            );
                            $foto_url       = $upload['secure_url'];
                            $foto_public_id = $upload['public_id'];

                            $stmt_foto = $conn->prepare("
                                INSERT INTO vistoria_fotos (id_vistoria_resultado, foto_url, foto_public_id)
                                VALUES (?, ?, ?)
                            ");
                            $stmt_foto->bind_param("iss", $id_vistoria_resultado, $foto_url, $foto_public_id);
                            $stmt_foto->execute();
                        }
                    }
                }
            }

            $conn->commit();
            $detalhes_log = "{$total_itens} " . ($total_itens === 1 ? 'item' : 'itens') . " vistoriado" . ($total_itens === 1 ? '' : 's');
            if ($total_ocorrencias > 0) {
                $detalhes_log .= ", {$total_ocorrencias} " . ($total_ocorrencias === 1 ? 'ocorrência aberta' : 'ocorrências abertas');
            }
            registrar_log($conn, $id_usuario, 'registrar_vistoria', 'reserva', $id, $detalhes_log);
            $sucesso_vistoria = "Vistoria registrada com sucesso!";

        } catch (Exception $e) {
            $conn->rollback();
            $erro_vistoria = "Erro ao registrar a vistoria. Tente novamente.";
        }

        fim_vistoria:
    }
}

// ─── Dados da vistoria (para exibir se já registrada) ────────────────────────
$vistoria_registrada = null;
if ($mostrar_vistoria) {
    $stmt_vr = $conn->prepare("
        SELECT vr.*, iv.nome_item, vf.foto_url
        FROM vistoria_resultados vr
        INNER JOIN itens_vistoria iv ON iv.id_item_vistoria = vr.id_item_vistoria
        LEFT JOIN vistoria_fotos vf ON vf.id_vistoria_resultado = vr.id_vistoria_resultado
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
if ($mostrar_vistoria && !$vistoria_registrada) {
    $res_itens = $conn->query("SELECT * FROM itens_vistoria WHERE ativo = 1 ORDER BY nome_item ASC");
    while ($row = $res_itens->fetch_assoc()) {
        $itens_vistoria_lista[] = $row;
    }
}

// Nomes para exibição somente-leitura em reservas passadas
$nome_ambiente_display   = '—';
$nome_funcionario_display = 'Não definido';
if ($is_passada) {
    $stmt_amb = $conn->prepare("SELECT nome_ambiente FROM ambientes WHERE id_ambiente = ?");
    $stmt_amb->bind_param("i", $reserva['id_ambiente']);
    $stmt_amb->execute();
    $row_amb = $stmt_amb->get_result()->fetch_assoc();
    if ($row_amb) $nome_ambiente_display = $row_amb['nome_ambiente'];

    if ($reserva['id_funcionario']) {
        $stmt_fnc = $conn->prepare("SELECT nome_completo FROM funcionarios WHERE id_funcionario = ?");
        $stmt_fnc->bind_param("i", $reserva['id_funcionario']);
        $stmt_fnc->execute();
        $row_fnc = $stmt_fnc->get_result()->fetch_assoc();
        if ($row_fnc) $nome_funcionario_display = $row_fnc['nome_completo'];
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

            <?php if ($is_passada): ?>

                <div class="alerta aviso">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Reserva já realizada, apenas os dados financeiros podem ser editados.
                </div>

                <!-- Informações somente-leitura -->
                <div class="form-cadastro reserva-dados-leitura">
                    <div class="form-grupo">
                        <label>Nome</label>
                        <input type="text" value="<?= htmlspecialchars($reserva['nome_reserva']) ?>" readonly tabindex="-1">
                    </div>
                    <div class="form-grupo">
                        <label>Telefone</label>
                        <input type="text" value="<?= htmlspecialchars($reserva['telefone_reserva'] ?? '') ?>" readonly tabindex="-1">
                    </div>
                    <div class="form-grupo">
                        <label>Data</label>
                        <input type="text" value="<?= fmt_data($reserva['data_reserva']) ?>" readonly tabindex="-1">
                    </div>
                    <div class="form-grupo">
                        <label>Horário</label>
                        <input type="text" value="<?= substr($reserva['hora_inicio'], 0, 5) ?> – <?= substr($reserva['hora_fim'], 0, 5) ?>" readonly tabindex="-1">
                    </div>
                    <div class="form-grupo">
                        <label>Ambiente</label>
                        <input type="text" value="<?= htmlspecialchars($nome_ambiente_display) ?>" readonly tabindex="-1">
                    </div>
                    <div class="form-grupo">
                        <label>Funcionário</label>
                        <input type="text" value="<?= htmlspecialchars($nome_funcionario_display) ?>" readonly tabindex="-1">
                    </div>
                </div>

                <!-- Formulário financeiro editável -->
                <form method="POST" class="form-cadastro">
                    <?= csrf_field() ?>
                    <input type="hidden" name="salvar_financeiro" value="1">

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
                        <textarea name="observacoes"><?= htmlspecialchars($reserva['observacoes'] ?? '') ?></textarea>
                    </div>
                    <div class="form-botoes">
                        <button class="btn btn-salvar">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Salvar
                        </button>
                        <a href="./index.php" class="btn btn-cancelar">Cancelar</a>
                    </div>
                </form>

            <?php else: ?>

                <form method="POST" class="form-cadastro">
                    <?= csrf_field() ?>
                    <input type="hidden" name="salvar_reserva" value="1">

                    <div class="form-grupo">
                        <label>Nome *</label>
                        <input type="text" name="nome_reserva" value="<?= htmlspecialchars($reserva['nome_reserva']) ?>" required>
                    </div>

                    <div class="form-grupo">
                        <label>Telefone *</label>
                        <input type="text" name="telefone_reserva" data-mask='(00) 00000 - 0000' value="<?= htmlspecialchars($reserva['telefone_reserva'] ?? '') ?>">
                    </div>

                    <div class="form-grupo">
                        <label>Data da reserva</label>
                        <input type="text" class="input-data" name="data_reserva" placeholder="DD/MM/AAAA" tabindex="-1" readonly required
                            value="<?= fmt_data($reserva['data_reserva']) ?>">
                    </div>

                    <div class="form-grupo">
                        <label>Hora de início *</label>
                        <input type="text" name="hora_inicio" class="input-hora" placeholder="HH:MM" maxlength="5" value="<?= substr($reserva['hora_inicio'], 0, 5) ?>" required>
                    </div>

                    <div class="form-grupo">
                        <label>Hora de término *</label>
                        <input type="text" name="hora_fim" class="input-hora" placeholder="HH:MM" maxlength="5" value="<?= substr($reserva['hora_fim'], 0, 5) ?>" required>
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
                        <textarea name="observacoes"><?= htmlspecialchars($reserva['observacoes'] ?? '') ?></textarea>
                    </div>

                    <div class="form-botoes">
                        <button class="btn btn-salvar">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Salvar
                        </button>
                        <a href="./index.php" class="btn btn-cancelar">Cancelar</a>
                    </div>

                </form>

            <?php endif; ?>

        </div>

        <?php if ($mostrar_vistoria): ?>
        <!-- ─── Bloco de Vistoria ─────────────────────────────────────── -->
        <div class="cadastro-area">

            <h3 class="vistoria-titulo">
                <i class="fa-solid fa-clipboard-list"></i>
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
                                <th>Foto</th>
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
                                    <td data-label="Foto">
                                        <?php if (!empty($vr['foto_url'])): ?>
                                            <img src="<?= htmlspecialchars($vr['foto_url']) ?>" class="vistoria-foto-thumb" alt="Foto do problema" onclick="abrirLightbox(this.src)">
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
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
                <form method="POST" enctype="multipart/form-data" class="form-cadastro">
                    <?= csrf_field() ?>
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
                            <div id="vi-problema-<?= $item['id_item_vistoria'] ?>" class="vi-problema">
                                <textarea
                                    name="descricao[<?= $item['id_item_vistoria'] ?>]"
                                    rows="2"
                                    placeholder="Descreva o problema encontrado..."
                                ></textarea>
                                <label class="vi-foto-label" for="foto-<?= $item['id_item_vistoria'] ?>">
                                    <i class="fa-solid fa-camera"></i>
                                    <span class="vi-foto-nome">Foto do problema (opcional)</span>
                                </label>
                                <input
                                    type="file"
                                    id="foto-<?= $item['id_item_vistoria'] ?>"
                                    name="foto[<?= $item['id_item_vistoria'] ?>]"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="vi-foto-input"
                                    onchange="atualizarNomeFoto(this, <?= $item['id_item_vistoria'] ?>)"
                                >
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
                    var ta   = wrap.querySelector('textarea');
                    var fi   = document.getElementById('foto-' + id);
                    if (!checkbox.checked) {
                        wrap.style.display = 'block';
                        ta.required = true;
                    } else {
                        wrap.style.display = 'none';
                        ta.required = false;
                        ta.value = '';
                        if (fi) { fi.value = ''; atualizarNomeFoto(fi, id); }
                    }
                }

                function atualizarNomeFoto(input, id) {
                    var span = document.querySelector('#vi-wrap-' + id + ' .vi-foto-nome');
                    if (!span) return;
                    span.textContent = input.files && input.files[0]
                        ? input.files[0].name
                        : 'Foto do problema (opcional)';
                }
                </script>
            <?php endif; ?>

        </div>
        <?php endif; ?>

    </main>
</div>

<?php include "../../includes/layout/footer.php"; ?>