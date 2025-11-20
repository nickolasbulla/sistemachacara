<?php
session_start();
require_once __DIR__ . "/../vendor/autoload.php"; // mpdf

include '../includes/login_verify.php';
include '../includes/db.php';

// validacao dos parametro
if (!isset($_GET['inicio']) || !isset($_GET['fim']) || !isset($_GET['id_ambiente'])) {
    die("Parâmetros inválidos.");
}

$inicio = $_GET['inicio'];
$fim = $_GET['fim'];
$id_ambiente = intval($_GET['id_ambiente']);

// pega nome do ambiente
$sqlAmb = $conn->prepare("SELECT nome_ambiente FROM ambientes WHERE id_ambiente = ?");
$sqlAmb->bind_param("i", $id_ambiente);
$sqlAmb->execute();
$nomeAmb = $sqlAmb->get_result()->fetch_assoc();

if (!$nomeAmb) {
    die("Ambiente não encontrado.");
}

/*busca reserva */

$sql = "
    SELECT 
        r.id_reserva,
        r.nome_reserva,
        r.telefone_reserva,
        r.data_reserva,
        r.hora_inicio,
        r.hora_fim,
        u.nome_completo AS usuario
    FROM reservas r
    JOIN usuarios u ON u.id_usuario = r.id_usuario
    WHERE r.id_ambiente = ?
    AND r.data_reserva BETWEEN ? AND ?
    ORDER BY r.data_reserva ASC, r.hora_inicio ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $id_ambiente, $inicio, $fim);
$stmt->execute();
$result = $stmt->get_result();


$html = '
<h1 style="text-align:center; color:#333; font-family:Arial; margin-bottom:5px;">
    Reservas do Ambiente: ' . htmlspecialchars($nomeAmb['nome_ambiente']) . '
</h1>

<p style="text-align:center; font-size:14px; margin-top:0;">
    Período: <strong>' . date("d/m/Y", strtotime($inicio)) . '</strong> 
    até 
    <strong>' . date("d/m/Y", strtotime($fim)) . '</strong>
</p>

<hr>

<table width="100%" border="1" cellspacing="0" cellpadding="8" 
       style="border-collapse: collapse; font-family:Arial; font-size:13px;">
    <thead style="background:#764ba2; color:white;">
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Telefone</th>
            <th>Data</th>
            <th>Horário</th>
            <th>Registrado por</th>
        </tr>
    </thead>
    <tbody>
';

if ($result->num_rows === 0) {
    $html .= '
        <tr>
            <td colspan="6" style="text-align:center; padding:20px;">
                Nenhuma reserva encontrada para este ambiente no período.
            </td>
        </tr>';
} else {
    while ($r = $result->fetch_assoc()) {
        $html .= '
        <tr>
            <td>' . $r['id_reserva'] . '</td>
            <td>' . htmlspecialchars($r['nome_reserva']) . '</td>
            <td>' . htmlspecialchars($r['telefone_reserva']) . '</td>
            <td>' . date("d/m/Y", strtotime($r['data_reserva'])) . '</td>
            <td>' . substr($r['hora_inicio'], 0, 5) . ' - ' . substr($r['hora_fim'], 0, 5) . '</td>
            <td>' . htmlspecialchars($r['usuario']) . '</td>
        </tr>';
    }
}

$html .= '
    </tbody>
</table>
';

// CRIA O PDF
$mpdf = new \Mpdf\Mpdf();

$mpdf->WriteHTML($html);

// baixa
$mpdf->Output("reservas_por_ambiente.pdf", "I");
exit;
