<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/db.php';

$titulo_pagina = "Usuários - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome_completo = $_POST["nome_completo"];
    $login = $_POST["login"];
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);
    $tipo_permissao = $_POST["tipo_permissao"];
    $data_nascimento = $_POST["data_nascimento"];
    $telefone = $_POST["telefone"];
    $observacoes = $_POST["observacoes"];
    $ativo = isset($_POST["ativo"]) ? 1 : 0;

    $check = $conn->prepare("SELECT id_usuario FROM usuarios WHERE login = ?");
    $check->bind_param("s", $login);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $erro = "Já existe um usuário com esse login!";
    } else {
        $stmt = $conn->prepare("INSERT INTO usuarios (nome_completo, login, senha, tipo_permissao, data_nascimento, telefone, observacoes, ativo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssi", $nome_completo, $login, $senha, $tipo_permissao, $data_nascimento, $telefone, $observacoes, $ativo);

        if ($stmt->execute()) {
            header("Location: index.php?sucesso=1");
            exit;
        } else {
            $erro = "Erro ao cadastrar o usuário. Tente novamente.";
        }

        if ($stmt->execute()) {
            header("Location: index.php?msg=Usuario cadastrado com sucesso!");
            exit;
        } else {
            $erro = "Erro ao cadastrar usuário: " . $conn->error;
        }
    }
}

?>

<div class="painel-container">

    <?php include '../../includes/layout/sidebar.php'; ?>
    <div class="sidebar-overlay"></div>

    <main class="conteudo">
        <header class="painel-header">
            <button class="menu-toggle">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1>Novo Usuário</h1>
        </header>

        <a href="./index.php" class="btn-voltar">
            <i class="fa-solid fa-arrow-left"></i>
            Voltar
        </a>

        <div class="cadastro-area">

            <?php if (!empty($erro)): ?>
                <div class="alerta erro"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro">
                <div class="form-grupo">
                    <label>Nome completo: *</label>
                    <input type="text" name="nome_completo" required>
                </div>

                <div class="form-grupo">
                    <label>Login: *</label>
                    <input type="text" name="login" required>
                </div>

                <div class="form-grupo">
                    <label>Senha: *</label>
                    <input type="password" name="senha" required>
                </div>

                <div class="form-grupo">
                    <label>Tipo de permissão: *</label>
                    <div class="select-wrapper">
                        <select name="tipo_permissao" required>
                            <option value="admin">Administrador</option>
                            <option value="reserveiro">Reserveiro</option>
                        </select>
                    </div>
                </div>

                <div class="form-grupo">
                    <label>Data de nascimento: *</label>
                    <input type="date" name="data_nascimento" min="1900-01-01" max="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Telefone:</label>
                    <input type="text" name="telefone" data-mask='(00) 00000 - 0000'>
                </div>

                <div class="form-grupo">
                    <label>Observações:</label>
                    <textarea name="observacoes" rows="3"></textarea>
                </div>

                <div class="form-grupo checkbox">
                    <label class="checkbox-container">
                        <input type="checkbox" name="ativo" checked>
                        <span class="checkmark"></span>
                        Usuário ativo
                    </label>
                </div>

                <div class="form-botoes">
                    <button class="btn btn-salvar">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Salvar
                    </button>
                    <a href="./index.php" class="btn btn-cancelar">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>

<?php include '../../includes/layout/footer.php'; ?>