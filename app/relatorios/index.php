<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/db.php';

$titulo_pagina = "Funcionários - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";
?>

<div class="painel-container">

    <?php include '../../includes/layout/sidebar.php'; ?>
    <div class="sidebar-overlay"></div>

    <main class="conteudo">

        <header class="painel-header">
            <button class="menu-toggle">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1>Relatórios</h1>
        </header>

        <div class="relatorios-grid">

            <!-- RELATÓRIO 1 -->
            <div class="relatorio-card">
                <h2>📄 Reservas não pagas</h2>
                <p>Lista de todas as reservas não pagas dentro de um período.</p>
                <a href="#" class="btn-relatorio btnRelatorio" data-relatorio="nao_pagas">Gerar PDF</a>
            </div>

            <!-- RELATÓRIO 2 -->
            <div class="relatorio-card">
                <h2>👤 Reservas por usuário</h2>
                <p>Mostra todas as reservas feitas por um usuário específico.</p>
                <a href="#" class="btn-relatorio btnRelatorio" data-relatorio="por_usuario">Gerar PDF</a>
            </div>

            <!-- RELATÓRIO 3 -->
            <div class="relatorio-card">
                <h2>🏡 Reservas por ambiente</h2>
                <p>Exibe quais ambientes estão reservados dentro de um período.</p>
                <a href="#" class="btn-relatorio btnRelatorio" data-relatorio="por_ambiente">Gerar PDF</a>
            </div>

        </div>

    </main>
</div>

<?php include '../../includes/layout/footer.php'; ?>