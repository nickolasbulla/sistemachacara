<?php
session_start();
include '../includes/login_verify.php';
include '../includes/db.php';
$titulo_pagina = "Ambientes - Chácara Portal";
$css_pagina = ["../assets/css/painel.css", "../assets/css/crud.css"];
include "../includes/header.php";

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
    $nome_ambiente = $_POST['nome_ambiente'];
    $capacidade = $_POST['capacidade'];
    $descricao = $_POST['descricao'];
    $observacoes = $_POST['observacoes'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;

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

        $stmt->bind_param("sissii", $nome_ambiente, $capacidade, $descricao, $observacoes, $ativo, $id);

        if ($stmt->execute()) {
            header("Location: index.php?sucesso=1");
            exit;
        } else {
            $erro = "Erro ao atualizar o ambiente.";
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
            <h1>Novo Ambiente</h1>
            <p>Cadastre um novo ambiente do sistema.</p>
        </header>

        <div class="cadastro-area">
            <a href="./index.php" class="btn-voltar">← Voltar</a>

            <?php if (!empty($erro)): ?>
                <div class="alerta erro"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro">
                <div class="form-grupo">
                    <label>Nome: *</label>
                    <input type="text" name="nome_ambiente" value="<?= htmlspecialchars($ambiente['nome_ambiente']) ?>"
                        required>
                </div>

                <div class="form-grupo">
                    <label>Capacidade: *</label>
                    <input type="number" name="capacidade" value="<?= htmlspecialchars($ambiente['capacidade']) ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Descrição: *</label>
                    <input type="text" name="descricao" value="<?= htmlspecialchars($ambiente['descricao']) ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Observações:</label>
                    <textarea name="observacoes" rows="3"><?= htmlspecialchars($ambiente['observacoes']) ?></textarea>
                </div>

                <div class="form-grupo checkbox">
                    <label>
                        <input type="checkbox" name="ativo" <?= $ambiente['ativo'] ? 'checked' : '' ?>> Ambiente ativo
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