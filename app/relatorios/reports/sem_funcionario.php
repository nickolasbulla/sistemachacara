<?php
$data_inicio = parse_data($_GET['data_inicio'] ?? '');
$data_fim    = parse_data($_GET['data_fim']    ?? '');

if (!$data_inicio || !$data_fim) {
    die('Parâmetros inválidos.');
}
if ($data_fim < $data_inicio) {
    die('A data fim não pode ser anterior à data início.');
}

$stmt = $conn->prepare("
    SELECT
        r.nome_reserva,
        r.data_reserva,
        r.hora_inicio,
        r.hora_fim,
        a.nome_ambiente,
        r.valor_cobrado,
        r.valor_pago,
        (r.valor_cobrado - r.valor_pago) AS em_aberto
    FROM reservas r
    INNER JOIN ambientes a ON a.id_ambiente = r.id_ambiente
    WHERE r.id_funcionario IS NULL
      AND r.data_reserva BETWEEN ? AND ?
    ORDER BY r.data_reserva ASC, r.hora_inicio ASC
");
$stmt->bind_param('ss', $data_inicio, $data_fim);
$stmt->execute();
$dados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total           = count($dados);
$total_cobrado   = array_sum(array_column($dados, 'valor_cobrado'));
$total_pago      = array_sum(array_column($dados, 'valor_pago'));
$total_em_aberto = array_sum(array_column($dados, 'em_aberto'));

$titulo  = 'Reservas sem Funcionário Atribuído';
$periodo = date('d/m/Y', strtotime($data_inicio)) . ' a ' . date('d/m/Y', strtotime($data_fim));

ob_start(); ?>

<div class="resumo">
    <table>
        <tr>
            <td class="destaque">
                <div class="label">Reservas sem funcionário</div>
                <div class="valor roxo"><?= $total ?></div>
            </td>
            <td>
                <div class="label">Total faturado</div>
                <div class="valor"><?= fmt($total_cobrado) ?></div>
            </td>
            <td>
                <div class="label">Total recebido</div>
                <div class="valor"><?= fmt($total_pago) ?></div>
            </td>
            <td>
                <div class="label">Total em aberto</div>
                <div class="valor"><?= fmt($total_em_aberto) ?></div>
            </td>
        </tr>
    </table>
</div>

<div class="dados">
    <?php if (empty($dados)): ?>
        <div class="sem-dados">Nenhuma reserva sem funcionário encontrada neste período.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr><td colspan="7" style="height:6mm;padding:0;border:none;background:white;"></td></tr>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Horário</th>
                    <th>Ambiente</th>
                    <th class="direita">Faturado</th>
                    <th class="direita">Em aberto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dados as $i => $r): ?>
                    <tr class="<?= ($i % 2 === 1) ? 'par' : '' ?>">
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($r['nome_reserva']) ?></td>
                        <td><?= date('d/m/Y', strtotime($r['data_reserva'])) ?></td>
                        <td><?= substr($r['hora_inicio'], 0, 5) ?> – <?= substr($r['hora_fim'], 0, 5) ?></td>
                        <td><?= htmlspecialchars($r['nome_ambiente']) ?></td>
                        <td><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($r['valor_cobrado'], 2, ',', '.') ?></td></tr></table></td>
                        <td <?= $r['em_aberto'] > 0 ? 'class="vermelho"' : '' ?>><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($r['em_aberto'], 2, ',', '.') ?></td></tr></table></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5">TOTAL (<?= $total ?> reserva<?= $total !== 1 ? 's' : '' ?>)</td>
                    <td><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($total_cobrado, 2, ',', '.') ?></td></tr></table></td>
                    <td class="vermelho"><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($total_em_aberto, 2, ',', '.') ?></td></tr></table></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>
</div>

<?php
$corpo = ob_get_clean();

$html = buildHtml($titulo, $periodo, $corpo);
renderPdf($html, "sem-funcionario_{$data_inicio}_{$data_fim}.pdf");
