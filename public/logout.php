<?php
session_start();
include '../config/init.php';

$id_usuario_logado = $_SESSION['usuario_id'] ?? null;

if ($id_usuario_logado) {
    registrar_log($conn, $id_usuario_logado, 'logout', 'sistema');
}
session_unset();
session_destroy();

// limpa o cookie de sessão para evitar que o browser envie um ID inválido no próximo request
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

$base = defined('BASE_URL') ? BASE_URL : '/';
$ts   = time();
header("Location: {$base}public/index.php?logout={$ts}");
exit;
