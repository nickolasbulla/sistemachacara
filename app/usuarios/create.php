<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/init.php';

$titulo_pagina = "Usuários - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    $nome_completo = trim($_POST["nome_completo"]);
    $login         = trim($_POST["login"]);
    $senha_raw     = $_POST["senha"];

    if (strlen($senha_raw) < 4) {
        $erro = "A senha deve ter no mínimo 4 caracteres.";
    }

    $senha = !isset($erro) ? password_hash($senha_raw, PASSWORD_DEFAULT) : '';
    $tipo_permissao  = $_POST["tipo_permissao"];
    $data_nascimento = parse_data($_POST["data_nascimento"]);
    $telefone        = trim($_POST["telefone"]);
    $observacoes     = trim($_POST["observacoes"]);
    $ativo = isset($_POST["ativo"]) ? 1 : 0;

    if (!isset($erro)) {
        $locked = $conn->query("SELECT GET_LOCK('usuarios_write', 5)")->fetch_row()[0];
        if (!$locked) {
            $erro = "Não foi possível processar agora. Tente novamente.";
        } else {
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
                    $conn->query("SELECT RELEASE_LOCK('usuarios_write')");
                    registrar_log($conn, $_SESSION['usuario_id'], 'criar', 'usuario', $conn->insert_id, $nome_completo);
                    header("Location: index.php?sucesso=1");
                    exit;
                } else {
                    $erro = "Erro ao cadastrar o usuário. Tente novamente.";
                }
            }
            $conn->query("SELECT RELEASE_LOCK('usuarios_write')");
        }
    } // fim if (!isset($erro))
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
                <?= csrf_field() ?>
                <div class="form-grupo">
                    <label>Nome completo: *</label>
                    <input type="text" name="nome_completo" value="<?= htmlspecialchars($_POST['nome_completo'] ?? '') ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Login: *</label>
                    <input type="text" name="login" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Senha: *</label>
                    <div class="password-wrapper">
                        <input type="password" name="senha" required>
                        <i class="fa-regular fa-eye toggle-password"></i>
                    </div>
                </div>

                <div class="form-grupo">
                    <label>Tipo de permissão: *</label>
                    <div class="select-wrapper">
                        <select name="tipo_permissao" required>
                            <option value="admin" <?= ($_POST['tipo_permissao'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                            <option value="reserveiro" <?= ($_POST['tipo_permissao'] ?? '') === 'reserveiro' ? 'selected' : '' ?>>Reserveiro</option>
                        </select>
                    </div>
                </div>

                <div class="form-grupo">
                    <label>Data de nascimento: *</label>
                    <input type="text" class="input-data" name="data_nascimento" placeholder="DD/MM/AAAA" value="<?= htmlspecialchars($_POST['data_nascimento'] ?? '') ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Telefone:</label>
                    <input type="text" name="telefone" data-mask='(00) 00000 - 0000' value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
                </div>

                <div class="form-grupo">
                    <label>Observações:</label>
                    <textarea name="observacoes" rows="3"><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
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