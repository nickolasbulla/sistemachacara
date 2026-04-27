<?php
session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Location: /sistemachacara/index.php');
    exit;
}
if ($_SESSION['usuario_tipo'] !== 'admin') {
    header('Location: /sistemachacara/public/logout.php');
    exit;
}

require_once '../../vendor/autoload.php';
include '../../config/db.php';

include '_base.php';
include '_layout.php';

$tipo = $_GET['tipo'] ?? '';

if (!preg_match('/^[a-z_]+$/', $tipo)) {
    die('Relatório inválido.');
}

$report = __DIR__ . "/reports/{$tipo}.php";

if (!file_exists($report)) {
    die('Relatório inválido.');
}

include $report;
