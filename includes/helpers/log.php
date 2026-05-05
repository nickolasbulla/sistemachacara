<?php

function registrar_log($conn, $id_usuario, $acao, $entidade, $id_registro = null, $detalhes = null) {
    $stmt = $conn->prepare("INSERT INTO logs_acoes (id_usuario, acao, entidade, id_registro, detalhes) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issis", $id_usuario, $acao, $entidade, $id_registro, $detalhes);
    $stmt->execute();
    $stmt->close();
}
