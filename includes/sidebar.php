<?php include '../includes/login_verify.php'; ?>

<aside class="sidebar">
    <div class="menu-topo">
        <h2>Painel</h2>
        <ul>
        <li><a href="../reservas/index.php">📅 Reservas</a></li>
        <li><a href="../relatorios/index.php">📊 Relatórios</a></li>

        <li class="submenu">
            <button class="submenu-toggle">📂 Cadastros <span class="seta">▾</span></button>
            <ul class="submenu-content">
            <li><a href="../usuarios/index.php">👤 Usuários</a></li>
            <li><a href="../funcionarios/index.php">👷 Funcionários</a></li>
            <li><a href="../ambientes/index.php">🏡 Ambientes</a></li>
            </ul>
        </li>
        </ul>
    </div>

    <div class="menu-bottom">
        <ul>
            <li><a href="#" id="btnPopup">🚪 Logout</a></li>
        </ul>
    </div>
</aside>

<!-- Popup de confirmação de logout -->
<div id="popupModal" class="popup-modal">
  <div class="popup-box">
    <h2>Deseja realmente sair?</h2>
    <p>Você será desconectado do sistema.</p>
    <div class="popup-buttons">
      <button id="cancelPopup" class="btn btn-cancelar">Cancelar</button>
      <a href="../logout.php" class="btn btn-confirmar">Sim, sair</a>
    </div>
  </div>
</div>


<!--

<div id="logoutModal" class="logout-modal">
<div class="logout-box">
    <h2>Deseja realmente sair?</h2>
    <p>Você será desconectado do sistema.</p>
    <div class="logout-buttons">
    <button id="cancelLogout" class="btn btn-cancelar">Cancelar</button>
    <a href="../logout.php" class="btn btn-confirmar">Sim, sair</a>
    </div>
</div>
</div>

-->