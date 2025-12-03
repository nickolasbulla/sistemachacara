<?php
session_start();
include '../includes/login_verify.php';
include '../includes/db.php';
$titulo_pagina = "Ambientes - Chácara Portal";
$css_pagina = ["../assets/css/painel.css", "../assets/css/crud.css"];
include '../includes/header.php';

//  lógica do delete aqui
if (isset($_GET['delete_id'])) {

    $id = (int) $_GET['delete_id'];

    try {
        $stmt = $conn->prepare("DELETE FROM ambientes WHERE id_ambiente = ?");
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
            <h1>Ambientes</h1>
            <p>Gerencie os ambientes do sistema.</p>
        </header>

        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1): ?>
            <div class="alerta sucesso">Ambiente cadastrado com sucesso!</div>
        <?php endif; ?>

        <?php if (isset($_GET['erro_relacionado'])): ?>
            <div class="alerta erro">Não é possível excluir: este registro está vinculado a uma ou mais reservas.</div>
        <?php endif; ?>

        <div class="area-crud">
            <a href="./create.php" class="btn btn-novo">+ Novo Ambiente</a>

            <?php
                $query = "SELECT * FROM ambientes ORDER BY id_ambiente DESC";
                $result = $conn->query($query);
            ?>
            <div class="tabela-wrapper">
                <table class="tabela-crud">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Capacidade</th>
                            <th>Descrição</th>
                            <th>Ativo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Nome"><?= htmlspecialchars($row['nome_ambiente']) ?></td>
                                <td data-label="Capacidade"><?= htmlspecialchars($row['capacidade']) ?></td>
                                <td data-label="Descrição"><?= $row['descricao'] ?></td>
                                <td data-label="Ativo"><?= $row['ativo'] ? '✅' : '❌' ?></td>
                                <td data-label="Ações">
                                    <a href="./edit.php?id=<?= $row['id_ambiente'] ?>" class="btn-editar">Selecionar</a>
                                    <a href="#" class="btn-excluir btnpopup" data-id="<?= $row['id_ambiente'] ?>">🗑️ Excluir</a>
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