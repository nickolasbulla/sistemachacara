<?php
session_start();

require_once '../../vendor/autoload.php';
include '../../includes/auth/login_verify.php';

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
