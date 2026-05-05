<?php
session_start();

require_once '../../vendor/autoload.php';
include '../../config/init.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . 'public/index.php');
    exit;
}
if ($_SESSION['usuario_tipo'] !== 'admin') {
    header('Location: ' . BASE_URL . 'public/logout.php');
    exit;
}

include '_base.php';
include '_layout.php';

$tipo = $_GET['tipo'] ?? '';

$relatorios_validos = [
    'faturamento',
    'por_usuario',
    'por_ambiente',
    'ocorrencias',
    'bloqueios',
    'sem_funcionario',
];

if (!in_array($tipo, $relatorios_validos, true)) {
    die('Relatório inválido.');
}

include __DIR__ . "/reports/{$tipo}.php";
