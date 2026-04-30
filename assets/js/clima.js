const Clima = (() => {
    const LAT = -23.4847;
    const LNG = -52.5681;

    const descricao = {
        0: 'Céu limpo', 1: 'Principalmente limpo', 2: 'Parcialmente nublado', 3: 'Encoberto',
        45: 'Neblina', 48: 'Neblina com gelo',
        51: 'Garoa leve', 53: 'Garoa moderada', 55: 'Garoa intensa',
        61: 'Chuva leve', 63: 'Chuva moderada', 65: 'Chuva intensa',
        71: 'Neve leve', 73: 'Neve moderada', 75: 'Neve intensa', 77: 'Granizo',
        80: 'Pancadas leves', 81: 'Pancadas moderadas', 82: 'Pancadas fortes',
        85: 'Neve com chuva', 86: 'Neve com chuva forte',
        95: 'Tempestade', 96: 'Tempestade com granizo', 99: 'Tempestade forte',
    };

    const icone = {
        0:  'wi-day-sunny',
        1:  'wi-day-sunny-overcast',
        2:  'wi-day-cloudy',
        3:  'wi-cloudy',
        45: 'wi-fog',
        48: 'wi-fog',
        51: 'wi-sprinkle',
        53: 'wi-showers',
        55: 'wi-rain',
        61: 'wi-rain-mix',
        63: 'wi-rain',
        65: 'wi-rain-wind',
        71: 'wi-snow-wind',
        73: 'wi-snow',
        75: 'wi-snow',
        77: 'wi-hail',
        80: 'wi-showers',
        81: 'wi-rain',
        82: 'wi-storm-showers',
        85: 'wi-rain-mix',
        86: 'wi-sleet',
        95: 'wi-thunderstorm',
        96: 'wi-thunderstorm',
        99: 'wi-thunderstorm',
    };

    const cache = {};

    async function buscar(data) {
        if (cache[data]) return cache[data];

        const hoje = new Date();
        hoje.setHours(0, 0, 0, 0);
        const dt = new Date(data + 'T12:00:00');
        const passado = dt < hoje;

        const base = passado
            ? 'https://archive-api.open-meteo.com/v1/archive'
            : 'https://api.open-meteo.com/v1/forecast';

        const params = new URLSearchParams({
            latitude: LAT, longitude: LNG,
            daily: 'temperature_2m_max,temperature_2m_min,precipitation_sum,weathercode',
            timezone: 'America/Sao_Paulo',
            start_date: data, end_date: data,
        });

        const resp = await fetch(`${base}?${params}`);
        if (!resp.ok) throw new Error('API error');

        const json = await resp.json();
        const d = json.daily;

        const tmax   = d.temperature_2m_max[0];
        const tmin   = d.temperature_2m_min[0];
        const codigo = d.weathercode[0];

        if (tmax === null || tmax === undefined || codigo === null || codigo === undefined) {
            throw new Error('Dados indisponíveis');
        }

        const resultado = {
            tmax:   Math.round(tmax),
            tmin:   Math.round(tmin),
            chuva:  d.precipitation_sum[0] ?? 0,
            codigo,
        };

        cache[data] = resultado;
        return resultado;
    }

    function html(dados) {
        const ic   = icone[dados.codigo]     ?? 'fa-temperature-half';
        const desc = descricao[dados.codigo] ?? 'Indisponível';
        const chuva = dados.chuva > 0 ? `${dados.chuva} mm` : 'Sem chuva';

        return `
            <div class="clima-widget">
                <i class="wi ${ic} clima-icone"></i>
                <div class="clima-dados">
                    <span class="clima-desc">${desc}</span>
                    <span class="clima-temp">${dados.tmin}° / ${dados.tmax}°C</span>
                    <span class="clima-chuva"><i class="fa-solid fa-droplet"></i> ${chuva}</span>
                </div>
            </div>`;
    }

    return { buscar, html };
})();
