<?php if (str_contains($body_class ?? '', 'painel-page')): ?>
<!-- Modal de inatividade -->
<div id="inatividade-modal" class="popup-modal">
    <div class="popup-box">
        <h2><i class="fa-solid fa-clock"></i> Sessão expirando</h2>
        <p>Você será desconectado por inatividade em <strong><span id="inatividade-contador">60</span>s</strong>.</p>
    </div>
</div>
<script>const BASE_URL = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>assets/js/inatividade.js"></script>
<?php endif; ?>
<script src="<?= BASE_URL ?>assets/js/painel.js"></script>
<script src="<?= BASE_URL ?>assets/js/popup.js"></script>
<script src="<?= BASE_URL ?>assets/js/clima.js"></script>
<script src="<?= BASE_URL ?>assets/js/feriados.js"></script>
<script src="<?= BASE_URL ?>assets/js/calendar.js"></script>
<script src="<?= BASE_URL ?>assets/js/pagamento.js"></script>
<script src="<?= BASE_URL ?>assets/js/login.js"></script>
<script src="<?= BASE_URL ?>assets/js/general.js"></script>

<!-- jquery somente para usar o datamask  -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $('.input-data').mask('00/00/0000');
    $('.input-hora').mask('00:00');
</script>

</body>
</html>