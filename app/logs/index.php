<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/db.php';

$titulo_pagina = "Logs - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";

// filtros
$filtro_acao     = $_GET['acao']     ?? '';
$filtro_entidade = $_GET['entidade'] ?? '';
$filtro_data     = $_GET['data']     ?? '';

$where = [];
$params = [];
$tipos  = '';

if ($filtro_acao) {
    $where[] = "l.acao = ?";
    $params[] = $filtro_acao;
    $tipos   .= 's';
}
if ($filtro_entidade) {
    $where[] = "l.entidade = ?";
    $params[] = $filtro_entidade;
    $tipos   .= 's';
}
if ($filtro_data) {
    $where[] = "DATE(l.data_hora) = ?";
    $params[] = parse_data($filtro_data);
    $tipos   .= 's';
}

$sql = "
    SELECT l.id_log, u.nome_completo, l.acao, l.entidade, l.id_registro, l.detalhes, l.data_hora
    FROM logs_acoes l
    INNER JOIN usuarios u ON u.id_usuario = l.id_usuario
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY l.data_hora DESC LIMIT 200";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$logs = $stmt->get_result();

$icones = [
    'criar'               => '<i class="fa-solid fa-plus log-icone-criar"></i>',
    'editar'              => '<i class="fa-solid fa-pen log-icone-editar"></i>',
    'excluir'             => '<i class="fa-solid fa-trash log-icone-excluir"></i>',
    'login'               => '<i class="fa-solid fa-right-to-bracket log-icone-login"></i>',
    'logout'              => '<i class="fa-solid fa-right-from-bracket log-icone-logout"></i>',
    'registrar_vistoria'  => '<i class="fa-solid fa-clipboard-check log-icone-vistoria"></i>',
];
?>

<div class="painel-container">

    <?php include '../../includes/layout/sidebar.php'; ?>
    <div class="sidebar-overlay"></div>

    <main class="conteudo">
        <header class="painel-header">
            <button class="menu-toggle">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1>Logs do Sistema</h1>
        </header>

        <!-- Filtros -->
        <form method="GET" class="form-cadastro logs-filtros">
            <div class="form-grupo">
                <label>Ação</label>
                <div class="select-wrapper">
                    <select name="acao">
                        <option value="">Todas</option>
                        <?php foreach (['criar','editar','excluir','login','logout','registrar_vistoria'] as $a): ?>
                            <option value="<?= $a ?>" <?= $filtro_acao === $a ? 'selected' : '' ?>><?= $a ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-grupo">
                <label>Entidade</label>
                <div class="select-wrapper">
                    <select name="entidade">
                        <option value="">Todas</option>
                        <?php foreach (['reserva','usuario','funcionario','ambiente','bloqueio','item_vistoria','ocorrencia','sistema'] as $e): ?>
                            <option value="<?= $e ?>" <?= $filtro_entidade === $e ? 'selected' : '' ?>><?= $e ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-grupo">
                <label>Data</label>
                <input type="text" class="input-data" name="data" placeholder="DD/MM/AAAA" value="<?= htmlspecialchars($filtro_data) ?>">
            </div>

            <div class="form-botoes">
                <button type="submit" class="btn btn-salvar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Filtrar
                </button>
                <a href="index.php" class="btn btn-cancelar">Limpar</a>
            </div>
        </form>

        <div class="tabela-wrapper">
            <table class="tabela-crud">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Usuário</th>
                        <th>Ação</th>
                        <th>Entidade</th>
                        <th>Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $logs->fetch_assoc()): ?>
                        <tr>
                            <td data-label="Data/Hora">
                                <?= date('d/m/Y H:i:s', strtotime($row['data_hora'])) ?>
                            </td>
                            <td data-label="Usuário">
                                <?= htmlspecialchars($row['nome_completo']) ?>
                            </td>
                            <td data-label="Ação">
                                <?= $icones[$row['acao']] ?? '' ?>
                                <?= htmlspecialchars($row['acao']) ?>
                            </td>
                            <td data-label="Entidade">
                                <?= htmlspecialchars($row['entidade']) ?>
                            </td>
                            <td data-label="Detalhes">
                                <?= $row['detalhes'] ? htmlspecialchars($row['detalhes']) : '—' ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<?php include '../../includes/layout/footer.php'; ?>