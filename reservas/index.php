<?php
session_start();
include '../includes/login_verify.php';

$titulo_pagina = "Reservas - Chácara Portal";
$css_pagina = "../assets/css/painel.css";
include '../includes/header.php';
?>

<div class="painel-container">

    <?php include '../includes/sidebar.php'; ?>

    <main class="conteudo">
        <header class="painel-header">
        <h1>Reservas</h1>
        <p>Gerencie todas as reservas cadastradas.</p>
        </header>

        <div id="area-reservas">

        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>