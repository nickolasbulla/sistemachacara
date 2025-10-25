<?php
// maneira de proteger páginas que precisam de autenticação

// desabilita cache da página (HTTP cache)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

// se não estiver logado, manda pro login
if (empty($_SESSION['usuario_id'])) {
    header('Location: /sistemachacara/index.php');
    exit;
}

?>

<script>   // trata o bfcache: assim o usuario não volta pro painel com o voltar do navegador
window.addEventListener('pageshow', function (e) {
  if (e.persisted) location.reload();
});
</script>