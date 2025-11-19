document.addEventListener('DOMContentLoaded', () => {
    const dias = document.querySelectorAll('.cal-dia');
    const modal = document.getElementById('calendarModal');
    const modalDataSpan = document.getElementById('calModalData');
    const modalLista = document.getElementById('calModalLista');
    const btnFechar = document.getElementById('fecharCalModal');

    dias.forEach(dia => {
        // se for vazio (espacinho antes do dia 1), ignora
        if (dia.classList.contains('vazio')) return;

        dia.addEventListener('click', () => {
            const data = dia.dataset.date;
            const hasReserva = dia.dataset.hasReserva === '1';

            // Dia livre vai pra create com a data
            if (!hasReserva) {
                window.location.href = 'create.php?data=' + data;
                return;
            }

            const count = parseInt(dia.dataset.reservasCount || '0', 10);

            // Só 1 reserva vai direto pro edit
            if (count === 1) {
                const id = dia.dataset.firstId;
                if (id) {
                    window.location.href = 'edit.php?id=' + id;
                }
                return;
            }

            // 2+ reservas abre popup com lista
            if (!modal || !modalLista || !modalDataSpan) return;

            // formata data YYYY-MM-DD -> DD/MM/YYYY
            if (data && data.includes('-')) {
                const [y, m, d] = data.split('-');
                modalDataSpan.textContent = `${d}/${m}/${y}`;
            } else {
                modalDataSpan.textContent = data;
            }

            modalLista.innerHTML = '';

            let reservas = [];
            try {
                reservas = JSON.parse(dia.dataset.reservas || '[]');
            } catch (e) {
                reservas = [];
            }

            if (!reservas.length) {
                modalLista.innerHTML = '<p>Não foi possível carregar as reservas.</p>';
            } else {
                reservas.forEach(r => {
                    const pagoTxt = r.pago == 1 ? 'Pago' : 'Pendente';
                    const item = document.createElement('div');
                    item.className = 'cal-reserva-item';
                    item.innerHTML = `
                        <div class="cal-reserva-info">
                            <strong>${r.nome_reserva}</strong><br>
                            <small>${r.hora_inicio} até ${r.hora_fim} — ${pagoTxt}</small>
                        </div>
                        <a href="edit.php?id=${r.id_reserva}" class="btn btn-editar">Abrir</a>
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

        // fechar clicando fora da caixa
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    }
});