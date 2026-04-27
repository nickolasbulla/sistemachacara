<?php

function parse_data(string $d): string {
    $dt = DateTime::createFromFormat('d/m/Y', trim($d));
    return $dt ? $dt->format('Y-m-d') : '';
}

function fmt_data(string $d): string {
    return $d ? date('d/m/Y', strtotime($d)) : '';
}

function registrar_log($conn, $id_usuario, $acao, $entidade, $id_registro = null, $detalhes = null) {
    $stmt = $conn->prepare("INSERT INTO logs_acoes (id_usuario, acao, entidade, id_registro, detalhes) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issis", $id_usuario, $acao, $entidade, $id_registro, $detalhes);
    $stmt->execute();
    $stmt->close();
}