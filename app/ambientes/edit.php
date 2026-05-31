<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/init.php';

$titulo_pagina = "Ambientes - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";

$id = $_GET['id'] ?? null;
$erro = '';
$sucesso = '';

if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM ambientes WHERE id_ambiente = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$ambiente = $result->fetch_assoc();

if (!$ambiente) {
    $erro = "Ambiente não encontrado.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $nome_ambiente = trim($_POST['nome_ambiente']);
    $capacidade    = (int) $_POST['capacidade'];
    $descricao     = trim($_POST['descricao']);
    $observacoes   = trim($_POST['observacoes']);
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    $locked = $conn->query("SELECT GET_LOCK('ambientes_write', 5)")->fetch_row()[0];
    if (!$locked) {
        $erro = "Não foi possível processar agora. Tente novamente.";
    } else {
        $check = $conn->prepare("SELECT id_ambiente FROM ambientes WHERE nome_ambiente = ? AND id_ambiente != ?");
        $check->bind_param("si", $nome_ambiente, $id);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            $erro = "Já existe outro ambiente com este nome.";
        } else {
            $stmt = $conn->prepare("UPDATE ambientes
                SET nome_ambiente=?, capacidade=?, descricao=?, observacoes=?, ativo=?
                WHERE id_ambiente=?");
            $stmt->bind_param("siisii", $nome_ambiente, $capacidade, $descricao, $observacoes, $ativo, $id);

            if ($stmt->execute()) {
                $conn->query("SELECT RELEASE_LOCK('ambientes_write')");
                registrar_log($conn, $_SESSION['usuario_id'], 'editar', 'ambiente', (int) $id, $nome_ambiente);
                header("Location: index.php?editado=1");
                exit;
            } else {
                $erro = "Erro ao atualizar o ambiente.";
            }
        }
        $conn->query("SELECT RELEASE_LOCK('ambientes_write')");
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
                    <label>Nome: *</label>
                    <input type="text" name="nome_ambiente" value="<?= htmlspecialchars($_POST['nome_ambiente'] ?? $ambiente['nome_ambiente'] ?? '') ?>"
                        required>
                </div>

                <div class="form-grupo">
                    <label>Capacidade: *</label>
                    <input type="number" name="capacidade" value="<?= htmlspecialchars($_POST['capacidade'] ?? $ambiente['capacidade'] ?? '') ?>"
                        required>
                </div>

                <div class="form-grupo">
                    <label>Descrição: *</label>
                    <textarea name="descricao" rows="3" required><?= htmlspecialchars($_POST['descricao'] ?? $ambiente['descricao'] ?? '') ?></textarea>
                </div>

                <div class="form-grupo">
                    <label>Observações:</label>
                    <textarea name="observacoes" rows="3"><?= htmlspecialchars($_POST['observacoes'] ?? $ambiente['observacoes'] ?? '') ?></textarea>
                </div>

                <div class="form-grupo checkbox">
                    <label>
                        <input type="checkbox" name="ativo" <?= (isset($_POST['ativo']) && $_SERVER['REQUEST_METHOD'] === 'POST' ? true : (bool)$ambiente['ativo']) ? 'checked' : '' ?>> Ambiente ativo
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