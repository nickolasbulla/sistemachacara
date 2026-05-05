<?php
require_once __DIR__ . '/../../config/init.php';

// desabilita cache da página
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . 'public/index.php');
    exit;
}

$tipo = $_SESSION['usuario_tipo']; // admin ou reserveiro
$url  = $_SERVER['REQUEST_URI'];   // página atual

if ($tipo !== 'admin') {

    $bloqueados = [
        '/usuarios',
        '/funcionarios',
        '/ambientes',
        '/relatorios',
        '/logs',
        '/ocorrencias'
    ];

    foreach ($bloqueados as $rota) {
        if (strpos($url, $rota) !== false) {
            header('Location: ' . BASE_URL . 'public/logout.php');
            exit;
        }
    }
}

?>
<script>
    /* trata o bfcache: assim o usuario não volta pro painel
    com o voltar do navegador depois de ter deslogado */
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) location.reload();
    });
</script>