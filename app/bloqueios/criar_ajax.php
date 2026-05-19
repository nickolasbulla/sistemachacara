<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/init.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SESSION['usuario_tipo'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método inválido.']);
    exit;
}

// CSRF manual para retornar JSON em vez de die()
$token = $_POST['csrf_token'] ?? '';
if (!$token || !hash_equals(csrf_token(), $token)) {
    http_response_code(403);
    echo json_encode(['erro' => 'Sessão expirada. Recarregue a página.']);
    exit;
}

$data_inicio = $_POST['data_inicio'] ?? '';
$data_fim    = $_POST['data_fim']    ?? '';
$motivo      = trim($_POST['motivo'] ?? '');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_inicio) ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_fim)) {
    echo json_encode(['erro' => 'Datas inválidas.']);
    exit;
}

if (empty($motivo)) {
    echo json_encode(['erro' => 'O motivo é obrigatório.']);
    exit;
}

if ($data_fim < $data_inicio) {
    [$data_inicio, $data_fim] = [$data_fim, $data_inicio];
}

$stmtChk = $conn->prepare("
    SELECT id_reserva, nome_reserva, data_reserva
    FROM reservas
    WHERE data_reserva BETWEEN ? AND ?
    LIMIT 1
");
$stmtChk->bind_param('ss', $data_inicio, $data_fim);
$stmtChk->execute();
$conflito = $stmtChk->get_result()->fetch_assoc();
$stmtChk->close();

if ($conflito) {
    $dataFmt = date('d/m/Y', strtotime($conflito['data_reserva']));
    $nome    = htmlspecialchars($conflito['nome_reserva']);
    echo json_encode(['erro' => "Conflito: já existe uma reserva em {$dataFmt} ({$nome})."]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO bloqueios (data_inicio, data_fim, motivo, id_usuario) VALUES (?, ?, ?, ?)");
$stmt->bind_param('sssi', $data_inicio, $data_fim, $motivo, $_SESSION['usuario_id']);

if ($stmt->execute()) {
    $id  = $conn->insert_id;
    $ini = date('d/m/Y', strtotime($data_inicio));
    $fim = date('d/m/Y', strtotime($data_fim));
    registrar_log($conn, $_SESSION['usuario_id'], 'criar', 'bloqueio', $id, "{$ini} até {$fim} | {$motivo}");
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['erro' => 'Erro ao salvar. Tente novamente.']);
}
