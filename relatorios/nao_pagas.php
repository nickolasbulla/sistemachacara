<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');
require_once __DIR__ . "/../vendor/autoload.php"; // mpdf

include '../includes/login_verify.php';
include '../includes/db.php';

// valida datas
if (!isset($_GET['inicio']) || !isset($_GET['fim'])) {
    die("Período inválido.");
}

$inicio = $_GET['inicio'];
$fim = $_GET['fim'];

// busca reservas não pagas no período
$sql = "
    SELECT 
        r.nome_reserva,
        r.data_reserva,
        r.valor_cobrado,
        r.valor_pago,
        a.nome_ambiente
    FROM reservas r
    JOIN ambientes a ON a.id_ambiente = r.id_ambiente
    WHERE r.valor_pago < r.valor_cobrado
    AND r.data_reserva BETWEEN ? AND ?
    ORDER BY r.data_reserva
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $inicio, $fim);
$stmt->execute();
$result = $stmt->get_result();

$html = '

<div style="text-align:center; margin-bottom:10px;">
    <img src="../assets/logo.jpg" width="120">
</div>

<h1 style="text-align:center; color:#333; font-family:Arial; margin-bottom:5px;">
    Relatório de Reservas Não Pagas
</h1>

<p style="text-align:center; font-size:14px; margin-top:0;">
    Período: <strong>' . date("d/m/Y", strtotime($inicio)) . '</strong> 
    até 
    <strong>' . date("d/m/Y", strtotime($fim)) . '</strong>
</p>

<table width="100%" border="1" cellspacing="0" cellpadding="8"
style="border-collapse: collapse; font-family:Arial; font-size:13px;">
    <thead style="background:#764ba2; color:white;">
        <tr>
            <th>Nome</th>
            <th>Data</th>
            <th>Ambiente</th>
            <th>Valor Cobrado</th>
            <th>Valor Pago</th>
            <th>Falta</th>
        </tr>
    </thead>
    <tbody>
';

if ($result->num_rows === 0) {
    $html .= '
        <tr>
            <td colspan="6" style="text-align:center; padding:20px;">
                Nenhuma reserva não paga encontrada neste período.
            </td>
        </tr>
    ';
} else {
    while ($r = $result->fetch_assoc()) {

        $falta = $r['valor_cobrado'] - $r['valor_pago'];

        $html .= '
        <tr>
            <td>' . htmlspecialchars($r['nome_reserva']) . '</td>
            <td style="text-align:center">' . date("d/m/Y", strtotime($r['data_reserva'])) . '</td>
            <td>' . htmlspecialchars($r['nome_ambiente']) . '</td>
            <td style="text-align:right">R$ ' . number_format($r['valor_cobrado'], 2, ',', '.') . '</td>
            <td style="text-align:right">R$ ' . number_format($r['valor_pago'], 2, ',', '.') . '</td>
            <td style="text-align:right"><strong>R$ ' . number_format($falta, 2, ',', '.') . '</strong></td>
        </tr>';
    }
}

$html .= '
    </tbody>
</table>
';

// instancia mpdf com fallback de pasta temporária
$mpdf = new \Mpdf\Mpdf([
    'tempDir' => __DIR__ . '/../tmp'
]);

$data_emissao = date("d/m/Y H:i:s");

$mpdf->SetFooter('
    <table width="100%" style="border-top: 0.5px solid #ccc; font-size: 14px;">
        <tr>
            <td style="text-align: left;">
                Relatório Gerado em: <strong>' . $data_emissao . '</strong>
            </td>
            <td style="text-align: right;">
                Página {PAGENO} de {nb}
            </td>
        </tr>
    </table>
');

$mpdf->WriteHTML($html);

// baixa o pdf
$mpdf->Output("reservas_nao_pagas.pdf", "I");
exit;