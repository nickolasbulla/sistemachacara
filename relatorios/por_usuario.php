<?php
session_start();
require_once __DIR__ . "/../vendor/autoload.php"; // mpdf

include '../includes/login_verify.php';
include '../includes/db.php';

// valida usuário
if (!isset($_GET['id_usuario']) || empty($_GET['id_usuario'])) {
    die("Usuário inválido.");
}

$id_usuario = intval($_GET['id_usuario']);

// busca nome do usuário
$sql_user = "SELECT nome_completo FROM usuarios WHERE id_usuario = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $id_usuario);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows === 0) {
    die("Usuário não encontrado.");
}

$usuario = $result_user->fetch_assoc()['nome_completo'];

// busca reservas do usuário
$sql = "
    SELECT 
        r.id_reserva,
        r.nome_reserva,
        r.telefone_reserva,
        r.data_reserva,
        r.hora_inicio,
        r.hora_fim,
        a.nome_ambiente
    FROM reservas r
    JOIN ambientes a ON a.id_ambiente = r.id_ambiente
    WHERE r.id_usuario = ?
    ORDER BY r.data_reserva, r.hora_inicio
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

// HTML do PDF
$html = '
<h1 style="text-align:center; color:#333; font-family:Arial; margin-bottom:5px;">
    Relatório de Reservas por Usuário
</h1>

<p style="text-align:center; font-size:14px; margin-top:0;">
    Usuário: <strong>' . htmlspecialchars($usuario) . '</strong>
</p>

<hr>

<table width="100%" border="1" cellspacing="0" cellpadding="8" style="border-collapse: collapse; font-family:Arial; font-size:13px;">
    <thead style="background:#764ba2; color:white;">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Telefone</th>
            <th>Data</th>
            <th>Horário</th>
            <th>Ambiente</th>
        </tr>
    </thead>
    <tbody>
';

// se não tiver reservas
if ($result->num_rows === 0) {
    $html .= '
        <tr>
            <td colspan="6" style="text-align:center; padding:20px;">
                Nenhuma reserva encontrada para este usuário.
            </td>
        </tr>
    ';
} else {
    while ($r = $result->fetch_assoc()) {

        $html .= '
        <tr>
            <td>' . $r['id_reserva'] . '</td>
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

// cria o PDF
$mpdf = new \Mpdf\Mpdf();

$mpdf->WriteHTML($html);

// baixa o PDF
$mpdf->Output("reservas_usuario.pdf", "I");
exit;