<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/db.php';

$titulo_pagina = "Usuários - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";

if (isset($_GET['delete_id'])) {

    $id = (int) $_GET['delete_id'];

    if ($id === $_SESSION['usuario_id']) {
        header("Location: index.php?erro=nao_pode_se_excluir");
        exit;
    }

    try {

        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
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

    <?php include '../../includes/layout/sidebar.php'; ?>

    <div class="sidebar-overlay"></div>

    <main class="conteudo">
        <header class="painel-header">
            <button class="menu-toggle">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1>Usuários</h1>
        </header>

        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1): ?>
            <div class="alerta sucesso">Usuário cadastrado com sucesso!</div>
        <?php endif; ?>

        <?php if (isset($_GET['erro_relacionado'])): ?>
            <div class="alerta erro">Não é possível excluir: este registro está vinculado a uma ou mais reservas.</div>
        <?php endif; ?>

        <div class="area-crud">
            <a href="./create.php" class="btn btn-novo">
                <i class="fa-solid fa-plus"></i>
                Novo Usuário
            </a>

            <?php
            $query = "SELECT * FROM usuarios ORDER BY id_usuario DESC";
            $result = $conn->query($query);
            ?>
            <div class="tabela-wrapper">
                <table class="tabela-crud">
                    <thead>
                        <tr>
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
                                <td data-label="Nome"><?= htmlspecialchars($row['nome_completo']) ?></td>
                                <td data-label="Login"><?= htmlspecialchars($row['login']) ?></td>
                                <td data-label="Permissão"><?= ($row['tipo_permissao']) ?></td>
                                <td data-label="Ativo">
                                    <?= $row['ativo'] ? '<i class="fa-solid fa-check"></i>' : '<i class="fa-solid fa-xmark"></i>' ?>
                                </td>
                                <td data-label="Ações">
                                    <a href="./edit.php?id=<?= $row['id_usuario'] ?>" class="btn-editar">
                                        <i class="fa-solid fa-hand-pointer"></i>
                                        Selecionar
                                    </a>
                                    <!-- usuario nao pode excluir ele mesmo, botão nao aparece. -->
                                    <?php if ($row['id_usuario'] != $_SESSION['usuario_id']): ?>
                                        <a href="#" class="btn-excluir btnpopup" data-id="<?= $row['id_usuario'] ?>">
                                            <i class="fa-solid fa-trash"></i>
                                            Excluir
                                        </a>
                                    <?php endif; ?>
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

<?php include '../../includes/layout/footer.php'; ?>