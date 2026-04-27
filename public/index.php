<?php
session_start();

$titulo_pagina = "Login - Chácara Portal";
$body_class = "login-page";
include '../includes/layout/header.php';
?>

<div class="login-container">
    <div class="login-header">
        <img src="../assets/images/logo2.png" alt="">
    </div>

    <!-- mensagem de erro -->
    <?php if (isset($_SESSION['erro_login'])): ?>
        <div class="alerta erro">
            <?= htmlspecialchars($_SESSION['erro_login']) ?>
        </div>
        <?php unset($_SESSION['erro_login']); ?>
    <?php endif; ?>

    <form action="autenticar.php" method="POST" id="login-form">
        <div class="form-group">
            <label for="usuario">Usuário</label>
            <input type="text" id="usuario" name="usuario" placeholder="Digite seu username" required
                autocomplete="off">
        </div>

        <div class="form-group password-group">
            <label for="senha">Senha</label>

            <div class="password-wrapper">
                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required
                    autocomplete="off">

                <i class="fa-regular fa-eye toggle-password" id="toggleSenha"></i>
            </div>
        </div>

        <button type="submit" class="btn-login" id="btnLogin">Entrar</button>
    </form>
</div>

<?php
include '../includes/layout/footer.php';
?>