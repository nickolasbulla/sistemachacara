<?php
session_start();
include '../config/db.php';

csrf_verify();

if (empty($_POST['usuario']) || empty($_POST['senha'])) {
    $_SESSION['erro_login'] = "Preencha todos os campos!";
    header("Location: index.php");
    exit;
}

$usuario = trim($_POST['usuario']);
$senha = trim($_POST['senha']);

$sql = "SELECT id_usuario, nome_completo, senha, tipo_permissao, ativo FROM usuarios WHERE login = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $user = $result->fetch_assoc();

    if ($user['ativo'] == 0) {
        $_SESSION['erro_login'] = "Usuário ou senha incorretos!";
        header("Location: index.php");
        exit;
    }

    if (password_verify($senha, $user['senha'])) {

        $_SESSION['usuario_id'] = $user['id_usuario'];
        $_SESSION['usuario_nome'] = $user['nome_completo'];
        $_SESSION['usuario_tipo'] = $user['tipo_permissao'];

        registrar_log($conn, $user['id_usuario'], 'login', 'sistema');
        session_write_close();
        header("Location: ../app/reservas/index.php");
        exit;
    } else {
        $_SESSION['erro_login'] = "Usuário ou senha incorretos!";
        header("Location: index.php");
        exit;
    }
} else {
    $_SESSION['erro_login'] = "Usuário ou senha incorretos!";
    header("Location: index.php");
    exit;
}

$stmt->close();
$conn->close();
?>