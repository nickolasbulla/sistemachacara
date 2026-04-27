<?php
$data_inicio = parse_data($_GET['data_inicio'] ?? '');
$data_fim    = parse_data($_GET['data_fim']    ?? '');
$status_filtro = $_GET['status'] ?? '';

if (!$data_inicio || !$data_fim) {
    die('Parâmetros inválidos.');
}

if ($status_filtro && !in_array($status_filtro, ['aberta', 'resolvida'])) {
    die('Parâmetros inválidos.');
}

$where_status = $status_filtro ? "AND o.status = ?" : '';

$sql = "
    SELECT
        o.id_ocorrencia,
        o.descricao,
        o.status,
        o.data_hora_registro,
        r.nome_reserva,
        r.data_reserva,
        iv.nome_item,
        u.nome_completo AS registrado_por
    FROM ocorrencias o
    INNER JOIN reservas r ON r.id_reserva = o.id_reserva
    INNER JOIN itens_vistoria iv ON iv.id_item_vistoria = o.id_item_vistoria
    INNER JOIN usuarios u ON u.id_usuario = o.id_usuario
    WHERE r.data_reserva BETWEEN ? AND ?
    $where_status
    ORDER BY r.data_reserva ASC, r.nome_reserva ASC, iv.nome_item ASC
";

$stmt = $conn->prepare($sql);

if ($status_filtro) {
    $stmt->bind_param('sss', $data_inicio, $data_fim, $status_filtro);
} else {
    $stmt->bind_param('ss', $data_inicio, $data_fim);
}

$stmt->execute();
$dados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total        = count($dados);
$total_abertas    = count(array_filter($dados, fn($r) => $r['status'] === 'aberta'));
$total_resolvidas = $total - $total_abertas;

$labels_status = ['aberta' => 'Abertas', 'resolvida' => 'Resolvidas', '' => 'Todas'];
$titulo  = 'Relatório de Ocorrências' . ($status_filtro ? ' — ' . $labels_status[$status_filtro] : '');
$periodo = date('d/m/Y', strtotime($data_inicio)) . ' a ' . date('d/m/Y', strtotime($data_fim));

ob_start(); ?>

<div class="resumo">
    <table>
        <tr>
            <td>
                <div class="label">Total de ocorrências</div>
                <div class="valor"><?= $total ?></div>
            </td>
            <td class="destaque">
                <div class="label">Abertas</div>
                <div class="valor roxo"><?= $total_abertas ?></div>
            </td>
            <td>
                <div class="label">Resolvidas</div>
                <div class="valor"><?= $total_resolvidas ?></div>
            </td>
        </tr>
    </table>
</div>

<div class="dados">
    <?php if (empty($dados)): ?>
        <div class="sem-dados">Nenhuma ocorrência encontrada neste período.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr><td colspan="6" style="height:6mm;padding:0;border:none;background:white;"></td></tr>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Data Reserva</th>
                    <th>Item</th>
                    <th>Descrição</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dados as $i => $r): ?>
                    <tr class="<?= ($i % 2 === 1) ? 'par' : '' ?>">
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($r['nome_reserva']) ?></td>
                        <td><?= date('d/m/Y', strtotime($r['data_reserva'])) ?></td>
                        <td><?= htmlspecialchars($r['nome_item']) ?></td>
                        <td><?= htmlspecialchars($r['descricao'] ?? '—') ?></td>
                        <td class="<?= $r['status'] === 'aberta' ? 'vermelho' : '' ?>">
                            <?= $r['status'] === 'aberta' ? 'Aberta' : 'Resolvida' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5">TOTAL (<?= $total ?> ocorrência<?= $total !== 1 ? 's' : '' ?>)</td>
                    <td><?= $total_abertas ?> aberta<?= $total_abertas !== 1 ? 's' : '' ?> / <?= $total_resolvidas ?> resolvida<?= $total_resolvidas !== 1 ? 's' : '' ?></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>
</div>

<?php
$corpo = ob_get_clean();

$slug_status = $status_filtro ?: 'todas';
$html = buildHtml($titulo, $periodo, $corpo);
renderPdf($html, "ocorrencias-{$slug_status}_{$data_inicio}_{$data_fim}.pdf");
