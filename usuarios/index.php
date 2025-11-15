<?php
session_start();
include '../includes/login_verify.php';
include '../includes/db.php';
$titulo_pagina = "Usuários - Chácara Portal";
$css_pagina = ["../assets/css/painel.css", "../assets/css/crud.css"];
include '../includes/header.php';

//  lógica do delete aqui
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM usuarios WHERE id_usuario = $id");
    header("Location: index.php?deletado=1");
    exit;
}
?>

<div class="painel-container">

    <?php include '../includes/sidebar.php'; ?>

    <div class="sidebar-overlay"></div>

    <main class="conteudo">
        <header class="painel-header">
            <button class="menu-toggle">☰</button>
            <h1>Usuários</h1>
            <p>Gerencie os usuários do sistema.</p>
        </header>

        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1): ?>
            <div class="alerta sucesso">Usuário cadastrado com sucesso!</div>
        <?php endif; ?>

        <div class="area-crud">
            <a href="./create.php" class="btn btn-novo">+ Novo Usuário</a>

            <?php
                $query = "SELECT * FROM usuarios ORDER BY id_usuario DESC";
                $result = $conn->query($query);
            ?>
            <div class="tabela-wrapper">
                <table class="tabela-crud">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome Completo</th>
                            <th>Login</th>
                            <th>Permissão</th>
                            <th>Ativo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td data-label="ID"><?= $row['id_usuario'] ?></td>
                                <td data-label="Nome"><?= htmlspecialchars($row['nome_completo']) ?></td>
                                <td data-label="Login"><?= htmlspecialchars($row['login']) ?></td>
                                <td data-label="Permissão"><?= ($row['tipo_permissao']) ?></td>
                                <td data-label="Ativo"><?= $row['ativo'] ? '✅' : '❌' ?></td>
                                <td data-label="Ações">
                                    <a href="./edit.php?id=<?= $row['id_usuario'] ?>" class="btn-editar">✏️Selecionar</a>
                                    <a href="#" class="btn-excluir btnPopup" data-id="<?= $row['id_usuario'] ?>">🗑️ Excluir</a>
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