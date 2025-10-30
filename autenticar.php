<?php
session_start();
include 'includes/conexao.php';

if (empty($_POST['usuario']) || empty($_POST['senha'])) {
    $_SESSION['erro_login'] = "Preencha todos os campos!";
    header("Location: index.php");
    exit;
}

//limpa entradas
$usuario = trim($_POST['usuario']);
$senha = trim($_POST['senha']);

//consulta no banco
$sql = "SELECT id_usuario, nome_completo, senha, tipo_permissao, ativo FROM usuarios WHERE login = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    //se tem linha é porque encontrou o usuário
    $user = $result->fetch_assoc();

    if ($user['ativo'] == 0) {
        $_SESSION['erro_login'] = "Usuário inativo!";
        header("Location: index.php");
        exit;
    }

    if (password_verify($senha, $user['senha'])) {
        // login ok: cria sessão
        $_SESSION['usuario_id'] = $user['id_usuario'];
        $_SESSION['usuario_nome'] = $user['nome_completo'];
        $_SESSION['usuario_tipo'] = $user['tipo_permissao'];

        session_write_close();
        header("Location: ./reservas/index.php");
        exit;
    } else {
        $_SESSION['erro_login'] = "Senha incorreta!";
        header("Location: index.php");
        exit;
    }
} else {
    $_SESSION['erro_login'] = "Usuário não encontrado!";
    header("Location: index.php");
    exit;
}

$stmt->close();
$conn->close();
?>