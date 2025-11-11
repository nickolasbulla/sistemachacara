<?php
include '../includes/db.php';
$titulo_pagina = "Usuários - Chácara Portal";
$css_pagina = ["../assets/css/painel.css", "../assets/css/crud.css"];
include "../includes/header.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $query = "DELETE FROM usuarios WHERE id_usuario = '$id'";
    $conn->query($query);

    // redireciona de volta pro index com mensagem de sucesso
    header('Location: index.php?sucesso_delete=1');
    exit;
}
?>

<!-- --- POPUP DE CONFIRMAÇÃO DE DELETE --- -->
<div id="popupModal" class="popup-modal">
  <div class="popup-box">
    <h2>Deseja realmente excluir?</h2>
    <p>Essa ação não poderá ser desfeita.</p>
    <div class="popup-buttons">
      <button id="cancelPopup" class="btn btn-cancelar">Cancelar</button>
      <a href="#" id="confirmPopup" class="btn btn-confirmar">Sim, excluir</a>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>