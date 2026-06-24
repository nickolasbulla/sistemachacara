<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/init.php';

$titulo_pagina = "Funcionários - Chácara Portal";
$body_class = "painel-page";
$usar_datepicker = true;
include "../../includes/layout/header.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    $nome_completo   = trim($_POST["nome_completo"]);
    $data_nascimento = parse_data($_POST["data_nascimento"]);
    $telefone        = trim($_POST["telefone"]);
    $observacoes     = trim($_POST["observacoes"]);
    $ativo = isset($_POST["ativo"]) ? 1 : 0;

    if ($data_nascimento) {
        $dezoito_anos = (new DateTime())->modify('-18 years')->format('Y-m-d');
        if ($data_nascimento > $dezoito_anos) {
            $erro = "O funcionário deve ter pelo menos 18 anos.";
        }
    } else {
        $erro = "Data de nascimento inválida.";
    }

    if (!isset($erro)) {
    $locked = $conn->query("SELECT GET_LOCK('funcionarios_write', 5)")->fetch_row()[0];
    if (!$locked) {
        $erro = "Não foi possível processar agora. Tente novamente.";
    } else {
        $check = $conn->prepare("SELECT id_funcionario FROM funcionarios WHERE nome_completo = ?");
        $check->bind_param("s", $nome_completo);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $erro = "Já existe um funcionário com esse nome!";
        } else {
            $stmt = $conn->prepare("INSERT INTO funcionarios (nome_completo,  data_nascimento, telefone, observacoes, ativo) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $nome_completo, $data_nascimento, $telefone, $observacoes, $ativo);

            if ($stmt->execute()) {
                $conn->query("SELECT RELEASE_LOCK('funcionarios_write')");
                registrar_log($conn, $_SESSION['usuario_id'], 'criar', 'funcionario', $conn->insert_id, $nome_completo);
                header("Location: index.php?sucesso=1");
                exit;
            } else {
                $erro = "Erro ao cadastrar o funcionário. Tente novamente.";
            }
        }
        $conn->query("SELECT RELEASE_LOCK('funcionarios_write')");
    }
    } // if (!isset($erro))
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
            <h1>Novo Funcionário</h1>
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
                    <label>Data de nascimento: *</label>
                    <input type="text" class="input-data" name="data_nascimento" placeholder="DD/MM/AAAA" value="<?= htmlspecialchars($_POST['data_nascimento'] ?? '') ?>" required data-datepicker>
                </div>

                <div class="form-grupo">
                    <label>Telefone: *</label>
                    <input type="text" name="telefone" data-mask='(00) 00000 - 0000' value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>" required>
                </div>

                <div class="form-grupo">
                    <label>Observações:</label>
                    <textarea name="observacoes" rows="3"><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
                </div>

                <div class="form-grupo checkbox">
                    <label>
                        <input type="checkbox" name="ativo" checked> Funcionário ativo
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