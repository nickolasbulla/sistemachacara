document.addEventListener('DOMContentLoaded', () => {

    // ── Elementos ───────────────────────────────────────────────────────────────
    const dias           = document.querySelectorAll('.cal-dia');
    const modal          = document.getElementById('calendarModal');
    const modalDataSpan  = document.getElementById('calModalData');
    const modalLista     = document.getElementById('calModalLista');
    const btnFechar      = document.getElementById('fecharCalModal');
    const btnNovaReserva = document.getElementById('btnNovaReservaDia');

    // ── Estado do modo bloqueio ─────────────────────────────────────────────────
    let bloqueioModeActive = false;
    let bloqueioInicio     = null;
    let bloqueioFim        = null;

    const btnToggle     = document.getElementById('btnBloqueioMode');
    const instrucaoEl   = document.getElementById('bloqueioInstrucao');
    const btnConfirmar  = document.getElementById('btnBloqueioConfirmar');
    const btnCancelar   = document.getElementById('btnBloqueioCancelar');

    const novoBloqueioModal  = document.getElementById('novoBloqueioModal');
    const novoBloqueioRange  = document.getElementById('novoBloqueioRange');
    const novoBloqueioMotivo = document.getElementById('novoBloqueioMotivo');
    const novoBloqueioSalvar = document.getElementById('novoBloqueioSalvar');
    const novoBloqueioFechar = document.getElementById('novoBloqueioFechar');
    const novoBloqueioErro   = document.getElementById('novoBloqueioErro');

    // ── Handlers de clique em cada dia ─────────────────────────────────────────
    dias.forEach(dia => {
        if (dia.classList.contains('vazio')) return;

        dia.addEventListener('click', () => {
            if (bloqueioModeActive) {
                selecionarParaBloqueio(dia);
                return;
            }
            if (dia.classList.contains('bloqueado')) {
                mostrarInfoBloqueio(dia);
            } else {
                mostrarReservasDia(dia);
            }
        });

        dia.addEventListener('mouseover', () => {
            if (!bloqueioModeActive || !bloqueioInicio || bloqueioFim !== null) return;
            previsualizarRange(dia.dataset.date);
        });

        dia.addEventListener('mouseenter', () => {
            if (!bloqueioModeActive) return;
            if (dia.classList.contains('bloqueado')) {
                dia.style.backgroundColor = '#ff5b4c';
            } else if (dia.classList.contains('reservado')) {
                dia.style.backgroundColor = 'rgba(102, 126, 234, 0.6)';
            }
        });

        dia.addEventListener('mouseleave', () => {
            if (!bloqueioModeActive) return;
            dia.style.backgroundColor = '';
        });
    });

    // ── Funções do modo bloqueio ────────────────────────────────────────────────
    function entrarModo() {
        bloqueioModeActive = true;
        btnToggle?.classList.add('ativo');
        document.querySelector('.calendar-container')?.classList.add('bloqueio-mode');
        btnCancelar?.classList.add('visivel');
        atualizarInstrucao();
    }

    function sairModo() {
        bloqueioModeActive = false;
        sessionStorage.removeItem('bloqueioState');
        btnToggle?.classList.remove('ativo');
        document.querySelector('.calendar-container')?.classList.remove('bloqueio-mode');
        btnCancelar?.classList.remove('visivel');
        dias.forEach(d => d.style.removeProperty('background-color'));
        resetarSelecao();
    }

    function resetarSelecao() {
        bloqueioInicio = null;
        bloqueioFim    = null;
        dias.forEach(d => d.classList.remove('bl-inicio', 'bl-fim', 'bl-range', 'bl-hover', 'bl-hover-invalido'));
        btnConfirmar?.classList.remove('visivel');
        atualizarInstrucao();
    }

    function atualizarInstrucao() {
        if (!instrucaoEl) return;
        if (!bloqueioModeActive)  { instrucaoEl.textContent = ''; return; }
        if (!bloqueioInicio)      instrucaoEl.textContent = 'Clique no dia inicial';
        else if (!bloqueioFim)    instrucaoEl.textContent = `Início: ${fmt(bloqueioInicio)} — clique no dia final`;
        else                      instrucaoEl.textContent = `${fmt(bloqueioInicio)} → ${fmt(bloqueioFim)}`;
    }

    function diaInvalido(dia) {
        return dia.dataset.hasReserva === '1' || dia.classList.contains('bloqueado');
    }

    function rangeTemConflito(start, end) {
        for (const d of dias) {
            const date = d.dataset.date;
            if (date && date >= start && date <= end && diaInvalido(d)) return true;
        }
        return false;
    }

    function selecionarParaBloqueio(dia) {
        const data = dia.dataset.date;
        if (!data) return;
        if (diaInvalido(dia)) return;

        if (dia.classList.contains('bl-inicio') || dia.classList.contains('bl-fim') || dia.classList.contains('bl-range')) {
            resetarSelecao();
            return;
        }

        if (!bloqueioInicio || bloqueioFim !== null) {
            resetarSelecao();
            bloqueioInicio = data;
            dia.classList.add('bl-inicio');
        } else {
            const start = data < bloqueioInicio ? data : bloqueioInicio;
            const end   = data < bloqueioInicio ? bloqueioInicio : data;

            if (rangeTemConflito(start, end)) return;

            bloqueioFim = data;
            if (bloqueioFim < bloqueioInicio) {
                [bloqueioInicio, bloqueioFim] = [bloqueioFim, bloqueioInicio];
            }
            marcarRange();
            btnConfirmar?.classList.add('visivel');
        }
        atualizarInstrucao();
    }

    function previsualizarRange(hoverDate) {
        if (!hoverDate) return;
        const start = hoverDate < bloqueioInicio ? hoverDate : bloqueioInicio;
        const end   = hoverDate < bloqueioInicio ? bloqueioInicio : hoverDate;

        const invalido = rangeTemConflito(start, end);

        dias.forEach(d => {
            const date = d.dataset.date;
            if (!date) return;
            d.classList.remove('bl-hover', 'bl-hover-invalido', 'bl-fim');
            if (date === bloqueioInicio) {
                d.classList.add('bl-inicio');
            } else if (date === end || (date > start && date < end)) {
                d.classList.add(invalido ? 'bl-hover-invalido' : 'bl-hover');
            }
        });
    }

    function marcarRange() {
        dias.forEach(d => {
            const date = d.dataset.date;
            if (!date) return;
            d.classList.remove('bl-inicio', 'bl-fim', 'bl-range', 'bl-hover', 'bl-hover-invalido');
            if (date === bloqueioInicio)                            d.classList.add('bl-inicio');
            else if (date === bloqueioFim)                         d.classList.add('bl-fim');
            else if (date > bloqueioInicio && date < bloqueioFim)  d.classList.add('bl-range');
        });
    }

    function fmt(str) {
        const [y, m, d] = str.split('-');
        return `${d}/${m}/${y}`;
    }

    // ── Persistência do modo bloqueio entre navegações de mês ──────────────────
    function salvarEstadoParaNavegacao() {
        if (bloqueioModeActive && bloqueioInicio && !bloqueioFim) {
            sessionStorage.setItem('bloqueioState', JSON.stringify({ inicio: bloqueioInicio }));
        }
    }

    document.querySelectorAll('.cal-nav').forEach(nav => {
        nav.addEventListener('click', salvarEstadoParaNavegacao);
    });

    document.querySelector('.cal-selector')?.addEventListener('submit', salvarEstadoParaNavegacao);

    // Restaurar estado salvo (navegou de mês com início já selecionado)
    const savedBl = JSON.parse(sessionStorage.getItem('bloqueioState') || 'null');
    if (savedBl?.inicio) {
        entrarModo();
        bloqueioInicio = savedBl.inicio;
        dias.forEach(d => { if (d.dataset.date === bloqueioInicio) d.classList.add('bl-inicio'); });
        atualizarInstrucao();
    }

    // ── Controles do modo bloqueio ──────────────────────────────────────────────
    btnToggle?.addEventListener('click', () => {
        bloqueioModeActive ? sairModo() : entrarModo();
    });

    btnCancelar?.addEventListener('click', sairModo);

    btnConfirmar?.addEventListener('click', () => {
        if (!bloqueioInicio || !bloqueioFim) return;
        const label = bloqueioInicio === bloqueioFim
            ? fmt(bloqueioInicio)
            : `${fmt(bloqueioInicio)} → ${fmt(bloqueioFim)}`;
        novoBloqueioRange.textContent  = label;
        novoBloqueioMotivo.value       = '';
        novoBloqueioErro.style.display = 'none';
        novoBloqueioErro.textContent   = '';
        novoBloqueioSalvar.disabled    = false;
        novoBloqueioSalvar.innerHTML   = '<i class="fa-solid fa-floppy-disk"></i> Salvar';
        novoBloqueioModal?.classList.add('active');
        novoBloqueioMotivo.focus();
    });

    novoBloqueioFechar?.addEventListener('click', () => {
        novoBloqueioModal?.classList.remove('active');
    });

    novoBloqueioModal?.addEventListener('click', e => {
        if (e.target === novoBloqueioModal) novoBloqueioModal.classList.remove('active');
    });

    novoBloqueioSalvar?.addEventListener('click', async () => {
        const motivo = novoBloqueioMotivo.value.trim();
        if (!motivo) {
            novoBloqueioErro.textContent   = 'O motivo é obrigatório.';
            novoBloqueioErro.style.display = 'block';
            return;
        }

        novoBloqueioSalvar.disabled   = true;
        novoBloqueioSalvar.innerHTML  = '<i class="fa-solid fa-spinner fa-spin"></i> Salvando...';
        novoBloqueioErro.style.display = 'none';

        const body = new FormData();
        body.append('csrf_token',  typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '');
        body.append('data_inicio', bloqueioInicio);
        body.append('data_fim',    bloqueioFim);
        body.append('motivo',      motivo);

        try {
            const resp = await fetch(`${BASE_URL}app/bloqueios/criar_ajax.php`, { method: 'POST', body });
            const json = await resp.json();
            if (json.ok) {
                sessionStorage.removeItem('bloqueioState');
                location.reload();
            } else {
                novoBloqueioErro.textContent   = json.erro || 'Erro ao salvar.';
                novoBloqueioErro.style.display = 'block';
                novoBloqueioSalvar.disabled    = false;
                novoBloqueioSalvar.innerHTML   = '<i class="fa-solid fa-floppy-disk"></i> Salvar';
            }
        } catch {
            novoBloqueioErro.textContent   = 'Erro de conexão. Tente novamente.';
            novoBloqueioErro.style.display = 'block';
            novoBloqueioSalvar.disabled    = false;
            novoBloqueioSalvar.innerHTML   = '<i class="fa-solid fa-floppy-disk"></i> Salvar';
        }
    });

    // ── Modal: reservas do dia ──────────────────────────────────────────────────
    function mostrarReservasDia(dia) {
        const data       = dia.dataset.date;
        const hasReserva = dia.dataset.hasReserva === '1';

        if (!modal || !modalLista || !modalDataSpan) return;

        modalDataSpan.textContent = fmt(data);
        if (btnNovaReserva) btnNovaReserva.href = `create.php?data=${data}`;

        const feriadoEl = document.getElementById('calModalFeriado');
        if (feriadoEl) {
            const nome = Feriados.getNome(data);
            feriadoEl.innerHTML = nome
                ? `<div class="cal-feriado-info"><i class="fa-solid fa-star"></i> ${nome}</div>`
                : '';
        }

        const climaEl = document.getElementById('calModalClima');
        if (climaEl) {
            climaEl.innerHTML = '<span class="clima-carregando"><i class="fa-solid fa-spinner fa-spin"></i> Carregando clima...</span>';
            Clima.buscar(data)
                .then(dados => { climaEl.innerHTML = Clima.html(dados); })
                .catch(()   => { climaEl.innerHTML = '<span class="clima-erro">Clima indisponível</span>'; });
        }

        modalLista.innerHTML = '';
        if (!hasReserva) {
            modalLista.innerHTML = '<p class="cal-sem-reserva">Nenhuma reserva para este dia.</p>';
        } else {
            let reservas = [];
            try { reservas = JSON.parse(dia.dataset.reservas || '[]'); } catch {}

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
    }

    // ── Modal: info de bloqueio existente ───────────────────────────────────────
    function mostrarInfoBloqueio(btn) {
        const bloqueios = JSON.parse(btn.dataset.bloqueios || '[]');
        const data      = btn.dataset.date;

        document.getElementById('bloqueioModalData').textContent = fmt(data);

        let html = '';
        bloqueios.forEach(b => {
            html += `
            <div class="bloqueio-item">
                <span class="bloqueio-periodo">
                    <i class="fa-solid fa-calendar-xmark"></i>
                    ${fmt(b.data_inicio)} &rarr; ${fmt(b.data_fim)}
                </span>
                <p class="bloqueio-motivo">${b.motivo}</p>
            </div>`;
        });
        document.getElementById('bloqueioModalLista').innerHTML = html;
        document.getElementById('bloqueioModal').classList.add('active');
    }

    // ── Fechar modais existentes ────────────────────────────────────────────────
    btnFechar?.addEventListener('click', () => modal?.classList.remove('active'));
    modal?.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('active'); });

    document.getElementById('fecharBloqueioModal')?.addEventListener('click', () => {
        document.getElementById('bloqueioModal').classList.remove('active');
    });
    document.getElementById('bloqueioModal')?.addEventListener('click', function (e) {
        if (e.target === this) this.classList.remove('active');
    });
});
