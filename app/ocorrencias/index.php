<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/db.php';

$titulo_pagina = "Ocorrências - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";
?>

<div class="painel-container">

    <?php include '../../includes/layout/sidebar.php'; ?>
    <div class="sidebar-overlay"></div>

    <main class="conteudo">
        <header class="painel-header">
            <button class="menu-toggle">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1>Ocorrências</h1>
        </header>

        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alerta sucesso">Ocorrência atualizada com sucesso!</div>
        <?php endif; ?>

        <?php if (isset($_GET['sucesso_vistoria'])): ?>
            <div class="alerta sucesso">Vistoria registrada com sucesso!</div>
        <?php endif; ?>

        <div class="area-crud">
            <?php
            $query = "
                SELECT
                    o.id_ocorrencia,
                    o.descricao,
                    o.status,
                    o.data_hora_registro,
                    r.nome_reserva,
                    r.data_reserva,
                    iv.nome_item,
                    u.nome_completo AS usuario_nome
                FROM ocorrencias o
                INNER JOIN reservas r ON r.id_reserva = o.id_reserva
                INNER JOIN itens_vistoria iv ON iv.id_item_vistoria = o.id_item_vistoria
                INNER JOIN usuarios u ON u.id_usuario = o.id_usuario
                ORDER BY o.data_hora_registro DESC
            ";
            $result = $conn->query($query);
            ?>
            <div class="tabela-wrapper">
                <table class="tabela-crud">
                    <thead>
                        <tr>
                            <th>Reserva</th>
                            <th>Data da Reserva</th>
                            <th>Item</th>
                            <th>Descrição</th>
                            <th>Status</th>
                            <th>Registrado por</th>
                            <th>Data do Registro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Reserva"><?= htmlspecialchars($row['nome_reserva']) ?></td>
                                <td data-label="Data da Reserva"><?= date('d/m/Y', strtotime($row['data_reserva'])) ?></td>
                                <td data-label="Item"><?= htmlspecialchars($row['nome_item']) ?></td>
                                <td data-label="Descrição"><?= htmlspecialchars($row['descricao']) ?></td>
                                <td data-label="Status">
                                    <span class="badge-status <?= $row['status'] === 'aberta' ? 'status-aberta' : 'status-resolvida' ?>">
                                        <?= $row['status'] === 'aberta' ? 'Aberta' : 'Resolvida' ?>
                                    </span>
                                </td>
                                <td data-label="Registrado por"><?= htmlspecialchars($row['usuario_nome']) ?></td>
                                <td data-label="Data do Registro"><?= date('d/m/Y H:i', strtotime($row['data_hora_registro'])) ?></td>
                                <td data-label="Ações">
                                    <a href="./edit.php?id=<?= $row['id_ocorrencia'] ?>" class="btn-editar">
                                        <i class="fa-solid fa-hand-pointer"></i>
                                        Selecionar
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include '../../includes/layout/footer.php'; ?>