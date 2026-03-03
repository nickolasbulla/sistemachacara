document.addEventListener('DOMContentLoaded', () => {

    const dias = document.querySelectorAll('.cal-dia');
    const modal = document.getElementById('calendarModal');
    const modalDataSpan = document.getElementById('calModalData');
    const modalLista = document.getElementById('calModalLista');
    const btnFechar = document.getElementById('fecharCalModal');
    const btnNovaReserva = document.getElementById('btnNovaReservaDia');

    dias.forEach(dia => {

        if (dia.classList.contains('vazio')) return;

        dia.addEventListener('click', () => {

            const data = dia.dataset.date;
            const hasReserva = dia.dataset.hasReserva === '1';

            if (!hasReserva) {
                window.location.href = 'create.php?data=' + data;
                return;
            }

            if (!modal || !modalLista || !modalDataSpan) return;

            if (data && data.includes('-')) {
                const [y, m, d] = data.split('-');
                modalDataSpan.textContent = `${d}/${m}/${y}`;
            } else {
                modalDataSpan.textContent = data;
            }

            if (btnNovaReserva) {
                btnNovaReserva.href = 'create.php?data=' + data;
            }

            modalLista.innerHTML = '';

            let reservas = [];
            try {
                reservas = JSON.parse(dia.dataset.reservas || '[]');
            } catch (e) {
                reservas = [];
            }

            if (!reservas.length) {
                modalLista.innerHTML =
                    '<p>Não foi possível carregar as reservas.</p>';
            } else {

                reservas.forEach(r => {

                    const valorPago = parseFloat(r.valor_pago || 0);
                    const valorCobrado = parseFloat(r.valor_cobrado || 0);
                    const pagoTxt =
                        valorPago >= valorCobrado ? 'Pago' : 'Pendente';

                    const item = document.createElement('div');
                    item.className = 'cal-reserva-item';

                    item.innerHTML = `
                        <div class="cal-reserva-info">
                            <strong>${r.nome_reserva}</strong><br>
                            <small>
                                ${r.hora_inicio} até ${r.hora_fim}
                                — ${pagoTxt}
                            </small>
                        </div>

                        <a href="edit.php?id=${r.id_reserva}"
                           class="btn btn-editar">
                           Abrir
                        </a>
                    `;

                    modalLista.appendChild(item);
                });
            }

            modal.classList.add('active');
        });
    });

    if (btnFechar && modal) {

        btnFechar.addEventListener('click', () => {
            modal.classList.remove('active');
        });

        // clicar fora fecha
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    }
});