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

// paginação
$por_pagina = 50;
$pagina     = max(1, (int) ($_GET['pagina'] ?? 1));
$offset     = ($pagina - 1) * $por_pagina;

$where  = [];
$params = [];
$tipos  = '';

if ($filtro_acao) {
    $where[]  = "l.acao = ?";
    $params[] = $filtro_acao;
    $tipos   .= 's';
}
if ($filtro_entidade) {
    $where[]  = "l.entidade = ?";
    $params[] = $filtro_entidade;
    $tipos   .= 's';
}
if ($filtro_data) {
    $where[]  = "DATE(l.data_hora) = ?";
    $params[] = parse_data($filtro_data);
    $tipos   .= 's';
}

$where_sql = $where ? " WHERE " . implode(" AND ", $where) : "";

// total de registros (para calcular páginas)
$sql_total = "SELECT COUNT(*)
              FROM logs_acoes l
              INNER JOIN usuarios u ON u.id_usuario = l.id_usuario"
             . $where_sql;

$stmt_t = $conn->prepare($sql_total);
if ($params) $stmt_t->bind_param($tipos, ...$params);
$stmt_t->execute();
$stmt_t->bind_result($total_registros);
$stmt_t->fetch();
$stmt_t->close();

$total_paginas = max(1, (int) ceil($total_registros / $por_pagina));
$pagina        = min($pagina, $total_paginas);
$offset        = ($pagina - 1) * $por_pagina;

// busca paginada
$sql = "SELECT l.id_log, u.nome_completo, l.acao, l.entidade, l.id_registro, l.detalhes, l.data_hora
        FROM logs_acoes l
        INNER JOIN usuarios u ON u.id_usuario = l.id_usuario"
       . $where_sql
       . " ORDER BY l.data_hora DESC
          LIMIT ? OFFSET ?";

$params_pag  = array_merge($params, [$por_pagina, $offset]);
$tipos_pag   = $tipos . 'ii';

$stmt = $conn->prepare($sql);
if ($params_pag) $stmt->bind_param($tipos_pag, ...$params_pag);
$stmt->execute();
$logs = $stmt->get_result();

// URL base preservando filtros (sem pagina)
$query_filtros = http_build_query(array_filter([
    'acao'     => $filtro_acao,
    'entidade' => $filtro_entidade,
    'data'     => $filtro_data,
]));
$base_url = 'index.php?' . ($query_filtros ? $query_filtros . '&' : '');

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

        <?php
        ob_start();
        if ($total_paginas > 1):
        $inicio = max(1, $pagina - 2);
        $fim    = min($total_paginas, $pagina + 2);
        ?>
        <div class="paginacao">
            <span class="paginacao-info">
                <?= number_format($total_registros, 0, ',', '.') ?> registros &mdash; página <?= $pagina ?> de <?= $total_paginas ?>
            </span>
            <div class="paginacao-links">
                <?php if ($pagina > 1): ?>
                    <a href="<?= $base_url ?>pagina=<?= $pagina - 1 ?>" class="pag-btn">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php if ($inicio > 1): ?>
                    <a href="<?= $base_url ?>pagina=1" class="pag-btn">1</a>
                    <?php if ($inicio > 2): ?><span class="pag-ellipsis">&hellip;</span><?php endif; ?>
                <?php endif; ?>

                <?php for ($p = $inicio; $p <= $fim; $p++): ?>
                    <a href="<?= $base_url ?>pagina=<?= $p ?>"
                       class="pag-btn <?= $p === $pagina ? 'ativo' : '' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>

                <?php if ($fim < $total_paginas): ?>
                    <?php if ($fim < $total_paginas - 1): ?><span class="pag-ellipsis">&hellip;</span><?php endif; ?>
                    <a href="<?= $base_url ?>pagina=<?= $total_paginas ?>" class="pag-btn"><?= $total_paginas ?></a>
                <?php endif; ?>

                <?php if ($pagina < $total_paginas): ?>
                    <a href="<?= $base_url ?>pagina=<?= $pagina + 1 ?>" class="pag-btn">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif;
        $paginacao_html = ob_get_clean();
        echo $paginacao_html;
        ?>

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

        <?= $paginacao_html ?>

    </main>
</div>

<?php include '../../includes/layout/footer.php'; ?>
