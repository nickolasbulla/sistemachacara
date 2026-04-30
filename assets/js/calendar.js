document.addEventListener('DOMContentLoaded', () => {

    const dias = document.querySelectorAll('.cal-dia');
    const modal = document.getElementById('calendarModal');
    const modalDataSpan = document.getElementById('calModalData');
    const modalLista = document.getElementById('calModalLista');
    const btnFechar = document.getElementById('fecharCalModal');
    const btnNovaReserva = document.getElementById('btnNovaReservaDia');

    dias.forEach(dia => {

        if (dia.classList.contains('vazio')) return;
        if (dia.classList.contains('bloqueado')) return;

        dia.addEventListener('click', () => {

            const data      = dia.dataset.date;
            const hasReserva = dia.dataset.hasReserva === '1';

            if (!modal || !modalLista || !modalDataSpan) return;

            if (data && data.includes('-')) {
                const [y, m, d] = data.split('-');
                modalDataSpan.textContent = `${d}/${m}/${y}`;
            } else {
                modalDataSpan.textContent = data;
            }

            if (btnNovaReserva) btnNovaReserva.href = 'create.php?data=' + data;

            // feriado
            const feriadoEl = document.getElementById('calModalFeriado');
            if (feriadoEl) {
                const nome = Feriados.getNome(data);
                feriadoEl.innerHTML = nome
                    ? `<div class="cal-feriado-info"><i class="fa-solid fa-star"></i> ${nome}</div>`
                    : '';
            }

            // clima
            const climaEl = document.getElementById('calModalClima');
            if (climaEl) {
                climaEl.innerHTML = '<span class="clima-carregando"><i class="fa-solid fa-spinner fa-spin"></i> Carregando clima...</span>';
                Clima.buscar(data)
                    .then(dados => { climaEl.innerHTML = Clima.html(dados); })
                    .catch(()  => { climaEl.innerHTML = '<span class="clima-erro">Clima indisponível</span>'; });
            }

            // reservas
            modalLista.innerHTML = '';

            if (!hasReserva) {
                modalLista.innerHTML = '<p class="cal-sem-reserva">Nenhuma reserva para este dia.</p>';
            } else {
                let reservas = [];
                try { reservas = JSON.parse(dia.dataset.reservas || '[]'); } catch (e) {}

                reservas.forEach(r => {
                    const item = document.createElement('div');
                    item.className = 'cal-reserva-item';
                    item.innerHTML = `
                        <div class="cal-reserva-info">
                            <strong>${r.nome_reserva}</strong><br>
                            <small>${r.hora_inicio} até ${r.hora_fim}${r.nome_ambiente ? ' — ' + r.nome_ambiente : ''}</small>
                        </div>
                        <a href="edit.php?id=${r.id_reserva}" class="btn btn-editar">Abrir</a>`;
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

    document.querySelectorAll('.cal-dia.bloqueado').forEach(btn => {
        btn.addEventListener('click', function () {
            const bloqueios = JSON.parse(this.dataset.bloqueios || '[]');
            const data = this.dataset.date;

            const [y, m, d] = data.split('-');
            document.getElementById('bloqueioModalData').textContent = `${d}/${m}/${y}`;

            // monta lista de bloqueios
            let html = '';
            bloqueios.forEach(b => {
                const ini = formatarData(b.data_inicio);
                const fim = formatarData(b.data_fim);
                html += `
                <div class="bloqueio-item">
                    <span class="bloqueio-periodo">
                        <i class="fa-solid fa-calendar-xmark"></i>
                        ${ini} &rarr; ${fim}
                    </span>
                    <p class="bloqueio-motivo">${b.motivo}</p>
                </div>
            `;
            });
            document.getElementById('bloqueioModalLista').innerHTML = html;

            document.getElementById('bloqueioModal').classList.add('active');
        });
    });

    document.getElementById('fecharBloqueioModal')?.addEventListener('click', () => {
        document.getElementById('bloqueioModal').classList.remove('active');
    });

    document.getElementById('bloqueioModal')?.addEventListener('click', function (e) {
        if (e.target === this) this.classList.remove('active');
    });

    function formatarData(str) {
        const [y, m, d] = str.split('-');
        return `${d}/${m}/${y}`;
    }
});