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
<div id="lightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.88);z-index:9999;align-items:center;justify-content:center;cursor:zoom-out;" onclick="this.style.display='none'">
    <img id="lightbox-img" src="" alt="" style="max-width:96vw;max-height:88vh;width:auto;height:auto;border-radius:8px;box-shadow:0 8px 40px rgba(0,0,0,0.5);pointer-events:none;">
    <button onclick="event.stopPropagation();baixarFoto()" style="position:absolute;bottom:24px;left:50%;transform:translateX(-50%);background:#fff;border:none;border-radius:8px;padding:10px 22px;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:8px;">
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
        fetch(src)
            .then(r => r.blob())
            .then(blob => {
                var url = URL.createObjectURL(blob);
                var a   = document.createElement('a');
                a.href  = url;
                a.download = 'foto_vistoria.jpg';
                a.click();
                URL.revokeObjectURL(url);
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