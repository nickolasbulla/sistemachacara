<?php
$data_inicio   = parse_data($_GET['data_inicio'] ?? '');
$data_fim      = parse_data($_GET['data_fim']    ?? '');
$status_filtro = $_GET['status'] ?? '';

if (!$data_inicio || !$data_fim) {
    die('Parâmetros inválidos.');
}

if ($status_filtro !== '' && !in_array($status_filtro, ['1', '0'])) {
    die('Parâmetros inválidos.');
}

$where_status = match($status_filtro) {
    '1'     => 'AND b.ativo = 1',
    '0'     => 'AND b.ativo = 0',
    default => '',
};

$stmt = $conn->prepare("
    SELECT
        b.id_bloqueio,
        b.data_inicio,
        b.data_fim,
        b.motivo,
        b.ativo,
        u.nome_completo AS cadastrado_por,
        DATEDIFF(
            LEAST(b.data_fim, ?),
            GREATEST(b.data_inicio, ?)
        ) + 1 AS dias_no_periodo
    FROM bloqueios b
    LEFT JOIN usuarios u ON u.id_usuario = b.id_usuario
    WHERE b.data_inicio <= ? AND b.data_fim >= ?
    $where_status
    ORDER BY b.data_inicio ASC
");
$stmt->bind_param('ssss', $data_fim, $data_inicio, $data_fim, $data_inicio);
$stmt->execute();
$dados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total         = count($dados);
$total_ativos  = count(array_filter($dados, fn($r) => $r['ativo'] == 1));
$total_inativos = $total - $total_ativos;
$total_dias    = array_sum(array_map(fn($r) => max(0, (int)$r['dias_no_periodo']), $dados));

$labels_status = ['1' => ' — Ativos', '0' => ' — Inativos', '' => ''];
$titulo  = 'Relatório de Datas Bloqueadas' . $labels_status[$status_filtro];
$periodo = date('d/m/Y', strtotime($data_inicio)) . ' a ' . date('d/m/Y', strtotime($data_fim));

ob_start(); ?>

<div class="resumo">
    <table>
        <tr>
            <td>
                <div class="label">Total de bloqueios</div>
                <div class="valor"><?= $total ?></div>
            </td>
            <td class="destaque">
                <div class="label">Ativos</div>
                <div class="valor roxo"><?= $total_ativos ?></div>
            </td>
            <td>
                <div class="label">Inativos</div>
                <div class="valor"><?= $total_inativos ?></div>
            </td>
            <td>
                <div class="label">Dias bloqueados no período</div>
                <div class="valor"><?= $total_dias ?></div>
            </td>
        </tr>
    </table>
</div>

<div class="dados">
    <?php if (empty($dados)): ?>
        <div class="sem-dados">Nenhum bloqueio encontrado neste período.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr><td colspan="6" style="height:6mm;padding:0;border:none;background:white;"></td></tr>
                <tr>
                    <th>#</th>
                    <th>Data início</th>
                    <th>Data fim</th>
                    <th>Motivo</th>
                    <th>Cadastrado por</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dados as $i => $b): ?>
                    <tr class="<?= ($i % 2 === 1) ? 'par' : '' ?>">
                        <td><?= $i + 1 ?></td>
                        <td><?= date('d/m/Y', strtotime($b['data_inicio'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($b['data_fim'])) ?></td>
                        <td><?= htmlspecialchars($b['motivo']) ?></td>
                        <td><?= htmlspecialchars($b['cadastrado_por'] ?? '—') ?></td>
                        <td class="<?= $b['ativo'] ? '' : 'vermelho' ?>">
                            <?= $b['ativo'] ? 'Ativo' : 'Inativo' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5">TOTAL (<?= $total ?> bloqueio<?= $total !== 1 ? 's' : '' ?>)</td>
                    <td><?= $total_ativos ?> ativo<?= $total_ativos !== 1 ? 's' : '' ?> / <?= $total_inativos ?> inativo<?= $total_inativos !== 1 ? 's' : '' ?></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>
</div>

<?php
$corpo = ob_get_clean();

$slug_status = match($status_filtro) { '1' => 'ativos', '0' => 'inativos', default => 'todos' };
$html = buildHtml($titulo, $periodo, $corpo);
renderPdf($html, "bloqueios-{$slug_status}_{$data_inicio}_{$data_fim}.pdf");
