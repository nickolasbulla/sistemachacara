<?php if (str_contains($body_class ?? '', 'painel-page') || str_contains($body_class ?? '', 'login-page')): ?>
    <!-- evita servir versão cacheada (bfcache) após navegação -->
    <script>
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) location.reload();
        });
    </script>

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

<!-- Lightbox -->
<div id="lightbox" style="display:none" onclick="this.style.display='none'">
    <img id="lightbox-img" src="" alt="">
    <button class="lightbox-baixar" onclick="event.stopPropagation();baixarFoto()">
        <i class="fa-solid fa-download"></i> Baixar foto
    </button>
</div>
<script>
    function abrirLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').style.display = 'flex';
    }
    function baixarFoto() {
        var src = document.getElementById('lightbox-img').src;
        fetch(src, { mode: 'cors' })
            .then(r => r.blob())
            .then(blob => {
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'foto_vistoria.jpg';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            })
            .catch(function () {
                window.open(src, '_blank');
            });
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') document.getElementById('lightbox').style.display = 'none';
    });
</script>

<script src="<?= BASE_URL ?>assets/js/painel.js"></script>
<script src="<?= BASE_URL ?>assets/js/popup.js"></script>
<script src="<?= BASE_URL ?>assets/js/clima.js"></script>
<script src="<?= BASE_URL ?>assets/js/feriados.js"></script>
<script src="<?= BASE_URL ?>assets/js/calendar.js"></script>
<script src="<?= BASE_URL ?>assets/js/pagamento.js"></script>
<script src="<?= BASE_URL ?>assets/js/general.js"></script>

<!-- jquery somente para usar o datamask  -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $('.input-data').mask('00/00/0000');
    $('.input-hora').mask('00:00');
</script>
<?php if (!empty($usar_datepicker)): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/pt.min.js"></script>
<script>
    flatpickr('[data-datepicker]', {
        dateFormat: 'd/m/Y',
        allowInput: true,
        locale: 'pt',
        maxDate: new Date(new Date().setFullYear(new Date().getFullYear() - 18)),
    });
</script>
<?php endif; ?>

</body>
</html>