<?php
$data_inicio = parse_data($_GET['data_inicio'] ?? '');
$data_fim    = parse_data($_GET['data_fim']    ?? '');
$id_ambiente = (int)($_GET['id_ambiente'] ?? 0);

if (!$data_inicio || !$data_fim) {
    die('Parâmetros inválidos.');
}

$periodo = date('d/m/Y', strtotime($data_inicio)) . ' a ' . date('d/m/Y', strtotime($data_fim));

// ── Ambiente específico ────────────────────────────────────────────────────
if ($id_ambiente) {
    $stmt = $conn->prepare("SELECT nome_ambiente FROM ambientes WHERE id_ambiente = ?");
    $stmt->bind_param('i', $id_ambiente);
    $stmt->execute();
    $ambiente = $stmt->get_result()->fetch_assoc();
    if (!$ambiente) die('Ambiente não encontrado.');

    $stmt = $conn->prepare("
        SELECT r.nome_reserva, r.data_reserva, r.hora_inicio, r.hora_fim,
               r.valor_cobrado, r.valor_pago,
               (r.valor_cobrado - r.valor_pago) AS em_aberto
        FROM reservas r
        WHERE r.id_ambiente = ? AND r.data_reserva BETWEEN ? AND ?
        ORDER BY r.data_reserva ASC
    ");
    $stmt->bind_param('iss', $id_ambiente, $data_inicio, $data_fim);
    $stmt->execute();
    $dados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $total          = count($dados);
    $total_cobrado  = array_sum(array_column($dados, 'valor_cobrado'));
    $total_pago     = array_sum(array_column($dados, 'valor_pago'));
    $total_em_aberto = array_sum(array_column($dados, 'em_aberto'));

    $titulo = 'Reservas — ' . htmlspecialchars($ambiente['nome_ambiente']);

    ob_start(); ?>

    <div class="resumo">
        <table>
            <tr>
                <td>
                    <div class="label">Total de reservas</div>
                    <div class="valor"><?= $total ?></div>
                </td>
                <td>
                    <div class="label">Total faturado</div>
                    <div class="valor"><?= fmt($total_cobrado) ?></div>
                </td>
                <td>
                    <div class="label">Total recebido</div>
                    <div class="valor"><?= fmt($total_pago) ?></div>
                </td>
                <td class="destaque">
                    <div class="label">Total em aberto</div>
                    <div class="valor roxo"><?= fmt($total_em_aberto) ?></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="dados">
        <?php if (empty($dados)): ?>
            <div class="sem-dados">Nenhuma reserva encontrada para este ambiente neste período.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr><td colspan="7" style="height:6mm;padding:0;border:none;background:white;"></td></tr>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th>Início</th>
                        <th class="direita">Faturado</th>
                        <th class="direita">Recebido</th>
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
                            <td><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($r['valor_cobrado'], 2, ',', '.') ?></td></tr></table></td>
                            <td><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($r['valor_pago'], 2, ',', '.') ?></td></tr></table></td>
                            <td <?= $r['em_aberto'] > 0 ? 'class="vermelho"' : '' ?>><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($r['em_aberto'], 2, ',', '.') ?></td></tr></table></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4">TOTAL (<?= $total ?> reserva<?= $total !== 1 ? 's' : '' ?>)</td>
                        <td><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($total_cobrado, 2, ',', '.') ?></td></tr></table></td>
                        <td><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($total_pago, 2, ',', '.') ?></td></tr></table></td>
                        <td class="vermelho"><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($total_em_aberto, 2, ',', '.') ?></td></tr></table></td>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>
    </div>

    <?php
    $corpo = ob_get_clean();
    $slug  = 'ambiente-' . $id_ambiente;

// ── Todos os ambientes ─────────────────────────────────────────────────────
} else {
    $stmt = $conn->prepare("
        SELECT
            a.nome_ambiente,
            COUNT(r.id_reserva)                  AS total_reservas,
            COALESCE(SUM(r.valor_cobrado), 0)     AS total_cobrado,
            COALESCE(SUM(r.valor_pago), 0)        AS total_pago,
            COALESCE(SUM(r.valor_cobrado - r.valor_pago), 0) AS em_aberto
        FROM ambientes a
        LEFT JOIN reservas r ON r.id_ambiente = a.id_ambiente
            AND r.data_reserva BETWEEN ? AND ?
        WHERE a.ativo = 1
        GROUP BY a.id_ambiente, a.nome_ambiente
        ORDER BY total_reservas DESC, a.nome_ambiente ASC
    ");
    $stmt->bind_param('ss', $data_inicio, $data_fim);
    $stmt->execute();
    $dados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $total_reservas  = array_sum(array_column($dados, 'total_reservas'));
    $total_cobrado   = array_sum(array_column($dados, 'total_cobrado'));
    $total_pago      = array_sum(array_column($dados, 'total_pago'));
    $total_em_aberto = array_sum(array_column($dados, 'em_aberto'));

    $titulo = 'Reservas por Ambiente — Todos';

    ob_start(); ?>

    <div class="resumo">
        <table>
            <tr>
                <td>
                    <div class="label">Total de reservas</div>
                    <div class="valor"><?= $total_reservas ?></div>
                </td>
                <td>
                    <div class="label">Total faturado</div>
                    <div class="valor"><?= fmt($total_cobrado) ?></div>
                </td>
                <td>
                    <div class="label">Total recebido</div>
                    <div class="valor"><?= fmt($total_pago) ?></div>
                </td>
                <td class="destaque">
                    <div class="label">Total em aberto</div>
                    <div class="valor roxo"><?= fmt($total_em_aberto) ?></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="dados">
        <table>
            <thead>
                <tr><td colspan="5" style="height:6mm;padding:0;border:none;background:white;"></td></tr>
                <tr>
                    <th>#</th>
                    <th>Ambiente</th>
                    <th class="direita">Reservas</th>
                    <th class="direita">Faturado</th>
                    <th class="direita">Em aberto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dados as $i => $a): ?>
                    <tr class="<?= ($i % 2 === 1) ? 'par' : '' ?>">
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($a['nome_ambiente']) ?></td>
                        <td class="direita"><?= $a['total_reservas'] ?></td>
                        <td><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($a['total_cobrado'], 2, ',', '.') ?></td></tr></table></td>
                        <td <?= $a['em_aberto'] > 0 ? 'class="vermelho"' : '' ?>><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($a['em_aberto'], 2, ',', '.') ?></td></tr></table></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">TOTAL (<?= count($dados) ?> ambiente<?= count($dados) !== 1 ? 's' : '' ?>)</td>
                    <td class="direita"><?= $total_reservas ?></td>
                    <td><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($total_cobrado, 2, ',', '.') ?></td></tr></table></td>
                    <td class="vermelho"><table class="moeda-cell"><tr><td class="rs">R$</td><td class="num"><?= number_format($total_em_aberto, 2, ',', '.') ?></td></tr></table></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <?php
    $corpo = ob_get_clean();
    $slug  = 'todos';
}

$html = buildHtml($titulo, $periodo, $corpo);
renderPdf($html, "reservas-por-ambiente-{$slug}_{$data_inicio}_{$data_fim}.pdf");
