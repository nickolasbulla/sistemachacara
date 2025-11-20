<?php
session_start();
include '../includes/login_verify.php';
include '../includes/db.php';
$titulo_pagina = "Relatórios - Chácara Portal";
$css_pagina = ["../assets/css/painel.css", "../assets/css/crud.css", "../assets/css/relatorios.css"];
include "../includes/header.php";
?>

<div class="painel-container">

    <?php include '../includes/sidebar.php'; ?>
    <div class="sidebar-overlay"></div>

    <main class="conteudo">

        <header class="painel-header">
            <button class="menu-toggle">☰</button>
            <h1>Relatórios</h1>
            <p>Gere arquivos PDF com informações do sistema.</p>
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

<!-- popup personalizado para os relatorios -->
<div id="relatorioModal" class="popup-modal">
    <div class="popup-box">
        <h2 id="tituloRelatorio">Gerar Relatório</h2>

        <form id="formRelatorio" method="GET" action="">
            
            <!-- Período -->
            <div id="campoPeriodo" style="display:none; margin-bottom: 15px;">
                <label>Data inicial:</label>
                <input type="date" name="inicio">

                <label style="margin-top:10px;">Data final:</label>
                <input type="date" name="fim">
            </div>

            <!-- Usuário -->
            <div id="campoUsuario" style="display:none; margin-bottom: 15px;">
                <label>Selecione o usuário:</label>
                <select name="id_usuario">
                    <option value="">Selecione</option>
                    <?php
                        $users = $conn->query("SELECT id_usuario, nome_completo FROM usuarios ORDER BY nome_completo");
                        while ($u = $users->fetch_assoc()):
                    ?>
                        <option value="<?= $u['id_usuario'] ?>"><?= $u['nome_completo'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div id="campoAmbiente" style="display:none; margin-bottom:15px;">
                <label>Selecione o ambiente:</label>
                <select name="id_ambiente" required>
                    <option value="">Selecione</option>
                    <?php
                        $ambs = $conn->query("SELECT id_ambiente, nome_ambiente FROM ambientes ORDER BY nome_ambiente");
                        while ($a = $ambs->fetch_assoc()):
                    ?>
                        <option value="<?= $a['id_ambiente'] ?>"><?= $a['nome_ambiente'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="popup-buttons" style="margin-top:20px;">
                <button type="button" id="cancelRelatorio" class="btn btn-cancelar">Cancelar</button>
                <button type="submit" class="btn btn-confirmar">Gerar PDF</button>
            </div>
        </form>

    </div>
</div>

<?php include "../includes/footer.php"; ?>