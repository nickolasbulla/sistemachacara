<?php
session_start();

include 'includes/login_verify.php';

$titulo_pagina = "Painel - Chácara Portal";
$css_pagina = "assets/css/painel.css";

include 'includes/header.php';
?>

<div class="painel-container">
    <aside class="sidebar">
  <div class="menu-topo">
    <h2>Painel</h2>
    <ul>
      <li><a href="painel.php" class="active">🏠 Início</a></li>

      <li><a href="reservas.php">📅 Reservas</a></li>

      <li class="submenu">
        <button class="submenu-toggle">
          📂 Cadastros <span class="seta">▾</span>
        </button>
        <ul class="submenu-content">
          <li><a href="usuarios.php">👤 Usuários</a></li>
          <li><a href="funcionarios.php">👷 Funcionários</a></li>
          <li><a href="ambientes.php">🏡 Ambientes</a></li>
        </ul>
      </li>
    </ul>
  </div>

  <div class="menu-bottom">
    <ul>
      <li><a href="relatorios.php">📊 Relatórios</a></li>
      <li><a href="#" id="btnLogout">🚪 Logout</a></li>
    </ul>
  </div>
</aside>

    <main class="conteudo">
        <header class="painel-header">
            <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</h1>
            <p>Você está logado como <strong><?php echo $_SESSION['usuario_tipo']; ?></strong>.</p>
        </header>
    </main>
</div>

<!-- Popup de confirmação de logout -->
<div id="logoutModal" class="logout-modal">
  <div class="logout-box">
    <h2>Deseja realmente sair?</h2>
    <p>Você será desconectado do sistema.</p>
    <div class="logout-buttons">
      <button id="cancelLogout" class="btn btn-cancelar">Cancelar</button>
      <a href="logout.php" class="btn btn-confirmar">Sim, sair</a>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>