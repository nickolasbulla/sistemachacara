const Feriados = (() => {
    const cache = {};

    async function buscar(ano) {
        if (cache[ano]) return cache[ano];
        const resp = await fetch(`https://brasilapi.com.br/api/feriados/v1/${ano}`);
        if (!resp.ok) throw new Error(`BrasilAPI erro ${resp.status}`);
        const lista = await resp.json();
        cache[ano] = {};
        lista.forEach(f => { cache[ano][f.date] = f.name; });
        console.log(`[Feriados] ${ano}: ${lista.length} feriados carregados`, cache[ano]);
        return cache[ano];
    }

    async function marcarCalendario() {
        const dias = document.querySelectorAll('.cal-dia[data-date]');
        if (!dias.length) return;

        const anos = new Set([...dias].map(d => d.dataset.date?.split('-')[0]).filter(Boolean));

        const feriadosMap = {};
        for (const ano of anos) {
            try {
                Object.assign(feriadosMap, await buscar(ano));
            } catch (e) {
                console.error('[Feriados] Falha ao buscar feriados:', e);
            }
        }

        let marcados = 0;
        dias.forEach(dia => {
            const data = dia.dataset.date;
            if (!feriadosMap[data]) return;
            dia.dataset.feriado = feriadosMap[data];
            const el = document.createElement('span');
            el.className = 'cal-feriado-nome';
            el.textContent = feriadosMap[data];
            dia.appendChild(el);
            marcados++;
        });
        console.log(`[Feriados] ${marcados} dias marcados no calendário.`);
    }

    function getNome(data) {
        for (const ano in cache) {
            if (cache[ano]?.[data]) return cache[ano][data];
        }
        return null;
    }

    return { marcarCalendario, getNome };
})();

// scripts no footer executam com o DOM já pronto
Feriados.marcarCalendario();
