<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/init.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

// divide em palavras e filtra vazios
$palavras = array_filter(array_map('trim', explode(' ', $q)), fn($p) => $p !== '');

if (empty($palavras)) {
    echo json_encode([]);
    exit;
}

// um LIKE por palavra, todos com AND
$condicoes = implode(' AND ', array_fill(0, count($palavras), 'r.nome_reserva LIKE ?'));
$tipos     = str_repeat('s', count($palavras));
$likes     = array_map(fn($p) => '%' . $p . '%', $palavras);

$stmt = $conn->prepare("
    SELECT r.id_reserva, r.nome_reserva, r.data_reserva,
           r.hora_inicio, r.hora_fim, a.nome_ambiente
    FROM reservas r
    LEFT JOIN ambientes a ON a.id_ambiente = r.id_ambiente
    WHERE {$condicoes}
    ORDER BY r.data_reserva DESC
    LIMIT 50
");
$stmt->bind_param($tipos, ...$likes);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode($rows);
