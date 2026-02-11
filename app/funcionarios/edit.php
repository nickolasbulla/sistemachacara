<?php
session_start();
include '../includes/login_verify.php';
include '../includes/db.php';
$titulo_pagina = "Funcionários - Chácara Portal";
$css_pagina = ["../assets/css/painel.css", "../assets/css/crud.css"];
include "../includes/header.php";

$id = $_GET['id'] ?? null;
$erro = '';
$sucesso = '';

if (!$id) {
    header('Location: index.php');
    exit;
}

// busca os dados do usuário
$stmt = $conn->prepare("SELECT * FROM funcionarios WHERE id_funcionario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$funcionario = $result->fetch_assoc();

if (!$funcionario) {
    $erro = "Funcionário não encontrado.";
}

// atualiza os dados ao enviar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome_completo = $_POST['nome_completo'];
    $data_nascimento = $_POST['data_nascimento'];
    $telefone = $_POST['telefone'];
    $observacoes = $_POST['observacoes'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    // verifica duplicidade
    $check = $conn->prepare("SELECT id_funcionario FROM funcionarios WHERE nome_completo = ? AND id_funcionario != ?");
    $check->bind_param("si", $nome_completo, $id);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows > 0) {
        $erro = "Já existe outro funcionário com este nome.";
    } 
    else {
        $update = $conn->prepare("
            UPDATE funcionarios 
            SET nome_completo=?, data_nascimento=?, telefone=?, observacoes=?, ativo=?
            WHERE id_funcionario=?
        ");

        $update->bind_param(
            "ssssii",
            $nome_completo,
            $data_nascimento,
            $telefone,
            $observacoes,
            $ativo,
            $id
        );

        if ($update->execute()) {
            header("Location: index.php?sucesso=1");
            exit;
        } else {
            $erro = "Erro ao atualizar o funcionário.";
        }
    }
}
?>

<div class="painel-container">

    <?php include '../includes/sidebar.php'; ?>
    <div class="sidebar-overlay"></div>

    <main class="conteudo">
        <header class="painel-header">
            <button class="menu-toggle">☰</button>
            <h1>Editar Funcionário</h1>
            <p>Atualize as informações deste funcionário.</p>
        </header>

        <div class="cadastro-area">
            <a href="./index.php" class="btn-voltar">← Voltar</a>

            <?php if (!empty($erro)) : ?>
                <div class="alerta erro"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro">
                <div class="form-grupo">
                    <label>Nome completo: *</label>
                    <input type="text" name="nome_completo" value="<?= htmlspecialchars($funcionario['nome_completo']) ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Data de nascimento: *</label>
                    <input type="date" name="data_nascimento" value="<?= htmlspecialchars($funcionario['data_nascimento']) ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Telefone: *</label>
                    <input type="text" name="telefone" data-mask='(00) 00000 - 0000' value="<?= htmlspecialchars($funcionario['telefone']) ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Observações:</label>
                    <textarea name="observacoes" rows="3"><?= htmlspecialchars($funcionario['observacoes']) ?></textarea>
                </div>

                <div class="form-grupo checkbox">
                    <label>
                        <input type="checkbox" name="ativo" <?= $funcionario['ativo'] ? 'checked' : '' ?>> Funcionário ativo
                    </label>
                </div>

                <div class="form-botoes">
                    <button type="submit" class="btn btn-salvar">💾 Salvar</button>
                    <a href="./index.php" class="btn btn-cancelar">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>

<?php include "../includes/footer.php"; ?>