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

// controle de permissao
$tipo = $_SESSION['usuario_tipo']; // admin ou reserveiro
$url  = $_SERVER['REQUEST_URI'];   // página atual

//reserveiro naopode acessar certas áreas
if ($tipo !== 'admin') {

    $bloqueados = [
        '/usuarios',
        '/funcionarios',
        '/ambientes',
        '/relatorios'
    ];

    foreach ($bloqueados as $rota) {
        if (strpos($url, $rota) !== false) {
            header("Location: /sistemachacara/reservas/index.php?erro=sem_permissao");
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