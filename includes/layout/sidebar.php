<aside class="sidebar">
    <div class="menu-topo">
        <div class="sidebar-usuario">
            <i class="fa-solid fa-circle-user"></i>
            <span><?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
        </div>
        <ul>

            <li>
                <a href="../reservas/index.php">
                    <i class="fa-solid fa-calendar-days"></i>
                    Reservas
                </a>
            </li>

            <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>

            <li>
                <a href="../ocorrencias/index.php">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Ocorrências
                </a>
            </li>

                <li>
                    <a href="../relatorios/index.php">
                        <i class="fa-solid fa-chart-column"></i>
                        Relatórios
                    </a>
                </li>
                <li class="submenu">
                    <button class="submenu-toggle">
                        <i class="fa-solid fa-folder-open"></i>
                        Cadastros
                    </button>

                    <ul class="submenu-content">
                        <li>
                            <a href="../usuarios/index.php">
                                <i class="fa-solid fa-user"></i>
                                Usuários
                            </a>
                        </li>
                        <li>
                            <a href="../funcionarios/index.php">
                                <i class="fa-solid fa-helmet-safety"></i>
                                Funcionários
                            </a>
                        </li>
                        <li>
                            <a href="../ambientes/index.php">
                                <i class="fa-solid fa-house"></i>
                                Ambientes
                            </a>
                        </li>
                        <li>
                            <a href="../bloqueios/index.php">
                                <i class="fa-solid fa-ban"></i>
                                Bloqueios
                            </a>
                        </li>
                        <li>
                            <a href="../vistoria/index.php">
                                <i class="fa-solid fa-clipboard-list"></i>
                                Vistoria
                            </a>
                        </li>
                    </ul>
                </li>

            <?php endif; ?>

        </ul>
    </div>

    <div class="menu-bottom">
        <ul>
            <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
            <li>
                <a href="../logs/index.php">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Logs
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="#" id="btnLogout">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    Sair
                </a>
            </li>
        </ul>
    </div>
</aside>

<!-- Popup de confirmação de LOGOUT -->
<div id="logoutModal" class="popup-modal">
    <div class="popup-box">
        <h2>Deseja realmente sair?</h2>
        <p>Você será desconectado do sistema.</p>

        <div class="popup-buttons">
            <button id="cancelLogout" class="btn btn-cancelar">
                Cancelar
            </button>

            <a href="../../public/logout.php" class="btn btn-confirmar">
                Sim, sair
            </a>
        </div>
    </div>
</div>