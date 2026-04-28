<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/db.php';

$titulo_pagina = "Usuários - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";

$id = $_GET['id'] ?? null;

$id_usuario_logado = $_SESSION['usuario_id'] ?? 0;
$permissao_logado = $_SESSION['usuario_tipo'] ?? '';
$is_self_edit = ($id == $id_usuario_logado);

$erro = '';
$sucesso = '';

if (!$id) {
    header('Location: index.php');
    exit;
}

$is_admin = ($permissao_logado === 'admin');
$is_admin_editing_other = ($is_admin && !$is_self_edit);

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    $erro = "Usuário não encontrado.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $nome_completo = $_POST['nome_completo'];
    $login = $_POST['login'];

    // campos de senah
    $senhaAtual = $_POST['senha_atual'] ?? '';
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    $tipo_permissao = $_POST['tipo_permissao'];
    $data_nascimento = parse_data($_POST['data_nascimento']);
    $telefone = $_POST['telefone'];
    $observacoes = $_POST['observacoes'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    $check = $conn->prepare("SELECT id_usuario FROM usuarios WHERE login = ? AND id_usuario != ?");
    $check->bind_param("si", $login, $id);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows > 0) {
        $erro = "Já existe outro usuário com este login.";
    }

    // verificar se vai alterar a senha
    $alterarSenha = !empty($senhaAtual) || !empty($novaSenha) || !empty($confirmarSenha);

    if (empty($erro) && $alterarSenha) {
        if (empty($novaSenha) || empty($confirmarSenha)) {
            $erro = "Para alterar a senha, preencha a Nova Senha e a Confirmação.";
        } elseif ($novaSenha !== $confirmarSenha) {
            $erro = "A nova senha e a confirmação não coincidem.";
        } elseif ($is_self_edit) {
            if (empty($senhaAtual)) {
                $erro = "Para alterar sua própria senha, você deve informar a Senha Atual.";
            } elseif (!password_verify($senhaAtual, $usuario['senha'])) {
                $erro = "A Senha Atual está incorreta.";
            }
        }
    }

    if (empty($erro)) {

        if ($alterarSenha) {
            $senha_hash = password_hash($novaSenha, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "UPDATE usuarios
                 SET nome_completo=?, login=?, senha=?, tipo_permissao=?, data_nascimento=?, telefone=?, observacoes=?, ativo=?
                 WHERE id_usuario=?"
            );
            $stmt->bind_param(
                "sssssssii",
                $nome_completo,
                $login,
                $senha_hash,
                $tipo_permissao,
                $data_nascimento,
                $telefone,
                $observacoes,
                $ativo,
                $id
            );

        } else {
            $stmt = $conn->prepare(
                "UPDATE usuarios
                 SET nome_completo=?, login=?, tipo_permissao=?, data_nascimento=?, telefone=?, observacoes=?, ativo=?
                 WHERE id_usuario=?"
            );
            $stmt->bind_param(
                "ssssssii",
                $nome_completo,
                $login,
                $tipo_permissao,
                $data_nascimento,
                $telefone,
                $observacoes,
                $ativo,
                $id
            );
        }

        if ($stmt->execute()) {
            registrar_log($conn, $_SESSION['usuario_id'], 'editar', 'usuario', (int) $id);
            header("Location: index.php?editado=1");
            exit;
        } else {
            $erro = "Erro ao atualizar o usuário.";
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
                    <input type="text" name="nome_completo" value="<?= htmlspecialchars($usuario['nome_completo']) ?>"
                        required>
                </div>

                <div class="form-grupo">
                    <label>Login: *</label>
                    <input type="text" name="login" value="<?= htmlspecialchars($usuario['login']) ?>" required>
                </div>

                <?php if ($is_self_edit): ?>
                    <div class="form-grupo">
                        <label>Senha atual:</label>
                        <input type="password" name="senha_atual" placeholder="Digite sua senha atual">
                    </div>
                    <div class="form-grupo">
                        <label>Nova senha:</label>
                        <input type="password" name="nova_senha" placeholder="Deixe em branco para não alterar">
                    </div>
                    <div class="form-grupo">
                        <label>Confirmar nova senha:</label>
                        <input type="password" name="confirmar_senha" placeholder="Repita a nova senha">
                    </div>
                <?php else: ?>
                    <div class="form-grupo">
                        <label>Redefinir senha:</label>
                        <input type="password" name="nova_senha" placeholder="Deixe em branco para não alterar">
                    </div>
                    <div class="form-grupo">
                        <label>Confirmar nova senha:</label>
                        <input type="password" name="confirmar_senha" placeholder="Repita a nova senha">
                    </div>
                <?php endif; ?>

                <div class="form-grupo">
                    <label>Tipo de permissão: *</label>
                    <div class="select-wrapper">
                        <select name="tipo_permissao" required>
                            <option value="admin" <?= $usuario['tipo_permissao'] === 'admin' ? 'selected' : '' ?>>
                                Administrador
                            </option>
                            <option value="reserveiro" <?= $usuario['tipo_permissao'] === 'reserveiro' ? 'selected' : '' ?>>
                                Reserveiro</option>
                        </select>
                    </div>
                </div>

                <div class="form-grupo">
                    <label>Data de nascimento: *</label>
                    <input type="text" class="input-data" name="data_nascimento" placeholder="DD/MM/AAAA" tabindex="-1"
                        value="<?= htmlspecialchars(fmt_data($usuario['data_nascimento'])) ?>">
                </div>

                <div class="form-grupo">
                    <label>Telefone:</label>
                    <input type="text" name="telefone" data-mask='(00) 00000 - 0000'
                        value="<?= htmlspecialchars($usuario['telefone']) ?>">
                </div>

                <div class="form-grupo">
                    <label>Observações:</label>
                    <textarea name="observacoes" rows="3"><?= htmlspecialchars($usuario['observacoes']) ?></textarea>
                </div>

                <div class="form-grupo checkbox">
                    <label>
                        <input type="checkbox" name="ativo" <?= $usuario['ativo'] ? 'checked' : '' ?>> Usuário ativo
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