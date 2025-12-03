<?php
session_start();
require_once __DIR__ . "/../vendor/autoload.php"; // mpdf

include '../includes/login_verify.php';
include '../includes/db.php';

// valida datas
if (!isset($_GET['inicio']) || !isset($_GET['fim'])) {
    die("Período inválido.");
}

$inicio = $_GET['inicio'];
$fim = $_GET['fim'];

// busca reservas nao pagas no período
$sql = "
    SELECT 
        r.nome_reserva,
        r.telefone_reserva,
        r.data_reserva,
        r.hora_inicio,
        r.hora_fim,
        a.nome_ambiente
    FROM reservas r
    JOIN ambientes a ON a.id_ambiente = r.id_ambiente
    WHERE r.pago = 0
    AND r.data_reserva BETWEEN ? AND ?
    ORDER BY r.data_reserva, r.hora_inicio
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

<hr>

<table width="100%" border="1" cellspacing="0" cellpadding="8" style="border-collapse: collapse; font-family:Arial; font-size:13px;">
    <thead style="background:#764ba2; color:white;">
        <tr>
            <th>Nome</th>
            <th>Telefone</th>
            <th>Data</th>
            <th>Horário</th>
            <th>Ambiente</th>
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

        $html .= '
        <tr>
            <td>' . htmlspecialchars($r['nome_reserva']) . '</td>
            <td>' . htmlspecialchars($r['telefone_reserva']) . '</td>
            <td>' . date("d/m/Y", strtotime($r['data_reserva'])) . '</td>
            <td>' . substr($r['hora_inicio'], 0, 5) . ' - ' . substr($r['hora_fim'], 0, 5) . '</td>
            <td>' . htmlspecialchars($r['nome_ambiente']) . '</td>
        </tr>';
    }
}

$html .= '
    </tbody>
</table>
';

$mpdf = new \Mpdf\Mpdf();

$mpdf->WriteHTML($html);

// baixa o pdf
$mpdf->Output("reservas_nao_pagas.pdf", "I");
exit;
