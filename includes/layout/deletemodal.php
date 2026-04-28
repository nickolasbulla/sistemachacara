<div id="deleteModal" class="popup-modal">
    <div class="popup-box">
        <h2>Deseja realmente excluir?</h2>
        <p>Essa ação não poderá ser desfeita.</p>
        <form id="deleteForm" method="POST" action="">
            <?= csrf_field() ?>
            <input type="hidden" name="delete_id" id="deleteId" value="">
            <div class="popup-buttons">
                <button type="button" id="cancelDelete" class="btn btn-cancelar">Cancelar</button>
                <button type="submit" class="btn btn-confirmar">Sim, excluir</button>
            </div>
        </form>
    </div>
</div>