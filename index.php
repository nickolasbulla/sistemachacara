<?php
    session_start();
    $titulo_pagina = "Login - Chácara Portal";
    $css_pagina = "assets/css/login.css";
    include 'includes/header.php';
?>

<div class="login-container">
    <div class="login-header">
        <h1>Chácara Portal</h1>
        <p>Gerenciamento de Aluguéis</p>
    </div>

    <!-- mensagem de erro -->
    <div id="errorMessage" class="error-message <?php echo isset($_SESSION['erro_login']) ? 'show' : ''; ?>">
        <?php
        if (isset($_SESSION['erro_login'])) {
            echo htmlspecialchars($_SESSION['erro_login']);
            unset($_SESSION['erro_login']);
        }
        ?>
    </div>

    <form action="autenticar.php" method="POST" id="login-form">
        <div class="form-group">
            <label for="usuario">Usuário</label>
            <input 
                type="text" 
                id="usuario" 
                name="usuario" 
                placeholder="Digite seu username"
                required
                autocomplete="off"
            >
        </div>
        
        <div class="form-group">
            <label for="senha">Senha</label>
            <input 
                type="password" 
                id="senha" 
                name="senha" 
                placeholder="Digite sua senha"
                required
                autocomplete="off"
            >
        </div>

        <button type="submit" class="btn-login" id="btnLogin">Entrar</button>
    </form>
</div>

<?php
    include 'includes/footer.php';
?>  