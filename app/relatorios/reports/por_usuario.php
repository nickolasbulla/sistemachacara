<?php
$id_usuario  = (int)($_GET['id_usuario']  ?? 0);
$data_inicio = parse_data($_GET['data_inicio'] ?? '');
$data_fim    = parse_data($_GET['data_fim']    ?? '');

if (!$id_usuario || !$data_inicio || !$data_fim) {
    die('Parâmetros inválidos.');
}
if ($data_fim < $data_inicio) {
    die('A data fim não pode ser anterior à data início.');
}

$stmt = $conn->prepare("SELECT login FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario) {
    die('Usuário não encontrado.');
}

$stmt = $conn->prepare("
    SELECT
        r.nome_reserva,
        r.data_reserva,
        a.nome_ambiente,
        r.valor_cobrado,
        r.valor_pago,
        (r.valor_cobrado - r.valor_pago) AS falta_pagar
    FROM reservas r
    INNER JOIN ambientes a ON r.id_ambiente = a.id_ambiente
    WHERE r.id_usuario = ?
      AND r.data_reserva BETWEEN ? AND ?
    ORDER BY r.data_reserva ASC
");
$stmt->bind_param('iss', $id_usuario, $data_inicio, $data_fim);
$stmt->execute();
$dados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total_cobrado = array_sum(array_column($dados, 'valor_cobrado'));
$total_pago    = array_sum(array_column($dados, 'valor_pago'));
$total_falta   = array_sum(array_column($dados, 'falta_pagar'));
$total_linhas  = count($dados);

$titulo  = 'Reservas do usuário: ' . htmlspecialchars($usuario['login']);
$periodo = date('d/m/Y', strtotime($data_inicio)) . ' a ' . date('d/m/Y', strtotime($data_fim));

ob_start(); ?>

<div class="resumo">
    <table>
        <tr>
            <td>
                <div class="label">Total de reservas</div>
                <div class="valor"><?= $total_linhas ?></div>
            </td>
            <td>
                <div class="label">Valor total cobrado</div>
                <div class="valor"><?= fmt($total_cobrado) ?></div>
            </td>
            <td>
                <div class="label">Total já pago</div>
                <div class="valor"><?= fmt($total_pago) ?></div>
            </td>
            <td class="destaque">
                <div class="label">Total em aberto</div>
                <div class="valor roxo"><?= fmt($total_falta) ?></div>
            </td>
        </tr>
    </table>
</div>

<div class="dados">
    <?php if (empty($dados)): ?>
        <div class="sem-dados">Nenhuma reserva encontrada para este usuário neste período.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr><td colspan="7" style="height:6mm;padding:0;border:none;background:white;"></td></tr>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Ambiente</th>
                    <th>Cobrado</th>
                    <th>Pago</th>
                    <th>Falta Pagar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dados as $i => $r): ?>
                    <tr class="<?= ($i % 2 === 1) ? 'par' : '' ?>">
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($r['nome_reserva']) ?></td>
                        <td><?= date('d/m/Y', strtotime($r['data_reserva'])) ?></td>
                        <td><?= htmlspecialchars($r['nome_ambiente']) ?></td>
                        <td><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($r['valor_cobrado'], 2, ',', '.') ?></td></tr></table></td>
                        <td><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($r['valor_pago'], 2, ',', '.') ?></td></tr></table></td>
                        <td class="vermelho"><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($r['falta_pagar'], 2, ',', '.') ?></td></tr></table></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tbody class="tfoot">
                <tr>
                    <td colspan="4">TOTAL (<?= $total_linhas ?> reserva<?= $total_linhas !== 1 ? 's' : '' ?>)</td>
                    <td><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($total_cobrado, 2, ',', '.') ?></td></tr></table></td>
                    <td><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($total_pago, 2, ',', '.') ?></td></tr></table></td>
                    <td class="vermelho"><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($total_falta, 2, ',', '.') ?></td></tr></table></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$corpo = ob_get_clean();

$html = buildHtml($titulo, $periodo, $corpo);
renderPdf($html, "reservas-{$usuario['login']}_{$data_inicio}_{$data_fim}.pdf");
