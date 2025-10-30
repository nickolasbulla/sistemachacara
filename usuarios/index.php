<?php
session_start();
include '../includes/login_verify.php';
include '../includes/conexao.php';

$titulo_pagina = "Usuários - Chácara Portal";
$css_pagina = [
"../assets/css/painel.css", "../assets/css/crud.css"
];
include '../includes/header.php';
?>

<div class="painel-container">

<?php include '../includes/sidebar.php'; ?>

<main class="conteudo">
    <header class="painel-header">
    <h1>Usuários</h1>
    <p>Gerencie os usuários do sistema.</p>
    </header>

    <div class="area-crud">
    <a href="usuarios_create.php" class="btn btn-novo">+ Novo Usuário</a>

    <?php
    $query = "SELECT * FROM usuarios ORDER BY id_usuario DESC";
    $result = $conn->query($query);
    ?>

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
            <td><?= $row['id_usuario'] ?></td>
            <td><?= htmlspecialchars($row['nome_completo']) ?></td>
            <td><?= htmlspecialchars($row['login']) ?></td>
            <td><?= ($row['tipo_permissao']) ?></td>
            <td><?= $row['ativo'] ? '✅' : '❌' ?></td>
            <td>
                <a href="edit.php?id=<?= $row['id_usuario'] ?>" class="btn-editar">✏️ Editar</a>
                <a href="delete.php?id=<?= $row['id_usuario'] ?>" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">🗑️ Excluir</a>
            </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</main>
</div>

<?php include '../includes/footer.php'; ?>