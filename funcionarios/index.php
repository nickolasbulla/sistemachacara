<?php
session_start();
include '../includes/login_verify.php';
include '../includes/db.php';
$titulo_pagina = "Funcionários - Chácara Portal";
$css_pagina = ["../assets/css/painel.css", "../assets/css/crud.css"];
include '../includes/header.php';

if (isset($_GET['delete_id'])) {

    $id = (int) $_GET['delete_id'];

    try {
        $stmt = $conn->prepare("DELETE FROM funcionarios WHERE id_funcionario = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        header("Location: index.php?deletado=1");
        exit;

    } catch (mysqli_sql_exception $e) {
        header("Location: index.php?erro_relacionado=1");
        exit;
    }
}
?>

<div class="painel-container">

    <?php include '../includes/sidebar.php'; ?>

    <div class="sidebar-overlay"></div>

    <main class="conteudo">
        <header class="painel-header">
            <button class="menu-toggle">☰</button>
            <h1>Funcionários</h1>
            <p>Gerencie os funcionários do sistema.</p>
        </header>

        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1): ?>
            <div class="alerta sucesso">Funcionário cadastrado com sucesso!</div>
        <?php endif; ?>

        <?php if (isset($_GET['erro_relacionado'])): ?>
            <div class="alerta erro">Não é possível excluir: este registro está vinculado a uma ou mais reservas.</div>
        <?php endif; ?>

        <div class="area-crud">
            <a href="./create.php" class="btn btn-novo">+ Novo Funcionário</a>

            <?php
                $query = "SELECT * FROM funcionarios ORDER BY id_funcionario DESC";
                $result = $conn->query($query);
            ?>
            <div class="tabela-wrapper">
                <table class="tabela-crud">
                    <thead>
                        <tr>
                            <th>Nome Completo</th>
                            <th>Telefone</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Nome"><?= htmlspecialchars($row['nome_completo']) ?></td>
                                <td data-label="Telefone"><?= $row['telefone'] ?></td>
                                <td data-label="Ações">
                                    <a href="./edit.php?id=<?= $row['id_funcionario'] ?>" class="btn-editar">Selecionar</a>
                                    <a href="#" class="btn-excluir btnpopup" data-id="<?= $row['id_funcionario'] ?>">🗑️ Excluir</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- pop up de excluir -->
<div id="deleteModal" class="popup-modal">
    <div class="popup-box">
        <h2>Deseja realmente excluir?</h2>
        <p>Essa ação não poderá ser desfeita.</p>
        <div class="popup-buttons">
            <button id="cancelDelete" class="btn btn-cancelar">Cancelar</button>
            <a href="#" id="confirmDelete" class="btn btn-confirmar">Sim, excluir</a>
        </div>
    </div>
</div>

<?php 
include '../includes/footer.php'; 
?>