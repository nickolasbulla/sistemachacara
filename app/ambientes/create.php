<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/db.php';

$titulo_pagina = "Ambientes - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome_ambiente = $_POST["nome_ambiente"];
    $capacidade = $_POST["capacidade"];
    $descricao = $_POST["descricao"];
    $observacoes = $_POST["observacoes"];
    $ativo = isset($_POST["ativo"]) ? 1 : 0;

    $check = $conn->prepare("SELECT id_ambiente FROM ambientes WHERE nome_ambiente = ?");
    $check->bind_param("s", $nome_ambiente);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $erro = "Já existe um ambiente com esse nome!";
    } else {
        $stmt = $conn->prepare("INSERT INTO ambientes (nome_ambiente,  capacidade, descricao, observacoes, ativo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $nome_ambiente, $capacidade, $descricao, $observacoes, $ativo);

        if ($stmt->execute()) {
            header("Location: index.php?sucesso=1");
            exit;
        } else {
            $erro = "Erro ao cadastrar o ambiente. Tente novamente.";
        }

        if ($stmt->execute()) {
            header("Location: index.php?msg=Ambiente cadastrado com sucesso!");
            exit;
        } else {
            $erro = "Erro ao cadastrar ambiente: " . $conn->error;
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
            <h1>Novo Ambiente</h1>
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
                    <label>Nome: *</label>
                    <input type="text" name="nome_ambiente" required>
                </div>

                <div class="form-grupo">
                    <label>Capacidade: *</label>
                    <input type="number" name="capacidade" required min="0">
                </div>

                <div class="form-grupo">
                    <label>Descrição: *</label>
                    <textarea name="descricao" rows="3" required></textarea>
                </div>

                <div class="form-grupo">
                    <label>Observações:</label>
                    <textarea name="observacoes" rows="3"></textarea>
                </div>

                <div class="form-grupo checkbox">
                    <label>
                        <input type="checkbox" name="ativo" checked> Ambiente ativo
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