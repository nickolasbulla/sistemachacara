<?php
session_start();
$id_usuario_logado = $_SESSION['usuario_id'] ?? null;

if ($id_usuario_logado) {
    include '../config/init.php';
    registrar_log($conn, $id_usuario_logado, 'logout', 'sistema');
}
session_unset();
session_destroy();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

$base = defined('BASE_URL') ? BASE_URL : '/';
header("Location: " . $base . "public/index.php?logout=" . time());
exit;
?>
