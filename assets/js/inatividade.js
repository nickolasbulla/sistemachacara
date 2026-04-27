(() => {
    const TEMPO_TOTAL    = 10 * 60 * 1000; // 10 minutos
    const TEMPO_AVISO    =  9 * 60 * 1000; //  9 minutos
    const LOGOUT_URL     = '/sistemachacara/public/logout.php';

    let timerAviso; 
    let timerLogout;
    let countdown;
    let segundosRestantes = 60;

    const modal       = document.getElementById('inatividade-modal');
    const spanContador = document.getElementById('inatividade-contador');

    function resetar() {
        clearTimeout(timerAviso);
        clearTimeout(timerLogout);
        clearInterval(countdown);
        esconderModal();

        timerAviso  = setTimeout(mostrarAviso,  TEMPO_AVISO);
        timerLogout = setTimeout(deslogar,       TEMPO_TOTAL);
    }

    function mostrarAviso() {
        segundosRestantes = 60;
        spanContador.textContent = segundosRestantes;
        modal.classList.add('active');

        countdown = setInterval(() => {
            segundosRestantes--;
            spanContador.textContent = segundosRestantes;
            if (segundosRestantes <= 0) {
                clearInterval(countdown);
            }
        }, 1000);
    }

    function esconderModal() {
        modal.classList.remove('active');
        clearInterval(countdown);
    }

    function deslogar() {
        window.location.href = LOGOUT_URL;
    }

    // eventos que indicam atividade
    ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart'].forEach(ev => {
        document.addEventListener(ev, resetar, { passive: true });
    });

    // inicia
    resetar();
})();