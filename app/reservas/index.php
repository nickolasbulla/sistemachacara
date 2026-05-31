<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/init.php';

$titulo_pagina = "Reservas - Chácara Portal";
$body_class = "painel-page page-calendario";
include "../../includes/layout/header.php";
include '../../includes/layout/deletemodal.php';

// calendario
$mesAtual = isset($_GET['mes']) ? (int) $_GET['mes'] : (int) date('m');
$anoAtual = isset($_GET['ano']) ? (int) $_GET['ano'] : (int) date('Y');

// ajusta para range 1-12
if ($mesAtual < 1) {
    $mesAtual = 12;
    $anoAtual--;
} elseif ($mesAtual > 12) {
    $mesAtual = 1;
    $anoAtual++;
}

$nomesMes = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];

$primeiroDiaMes = strtotime("$anoAtual-$mesAtual-01");
$nomeMesAtual = $nomesMes[$mesAtual] ?? 'Mês';
$qtdeDiasMes = (int) date('t', $primeiroDiaMes);
$diaSemanaPrimeiro = (int) date('w', $primeiroDiaMes);

// navegação anterior / próximo
$mesAnterior = $mesAtual - 1;
$anoAnterior = $anoAtual;
if ($mesAnterior < 1) {
    $mesAnterior = 12;
    $anoAnterior--;
}

$mesProximo = $mesAtual + 1;
$anoProximo = $anoAtual;
if ($mesProximo > 12) {
    $mesProximo = 1;
    $anoProximo++;
}

// busca das reservas do mês
$inicioMes = date('Y-m-01', $primeiroDiaMes);
$fimMes    = date('Y-m-t',  $primeiroDiaMes);

$sql = "SELECT r.id_reserva, r.nome_reserva, r.data_reserva, r.hora_inicio, r.hora_fim,
               r.valor_cobrado, r.valor_pago, a.nome_ambiente
        FROM reservas r
        LEFT JOIN ambientes a ON a.id_ambiente = r.id_ambiente
        WHERE r.data_reserva BETWEEN ? AND ?
        ORDER BY r.data_reserva, r.hora_inicio";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $inicioMes, $fimMes);
$stmt->execute();
$result = $stmt->get_result();

$reservasPorDia = [];
while ($row = $result->fetch_assoc()) {
    $data = $row['data_reserva'];
    if (!isset($reservasPorDia[$data])) {
        $reservasPorDia[$data] = [];
    }
    $reservasPorDia[$data][] = [
        'id_reserva'    => $row['id_reserva'],
        'nome_reserva'  => $row['nome_reserva'],
        'hora_inicio'   => substr($row['hora_inicio'], 0, 5),
        'hora_fim'      => substr($row['hora_fim'], 0, 5),
        'nome_ambiente' => $row['nome_ambiente'] ?? '',
    ];
}

$stmt->close();

// busca dos bloqueios ativos que tocam o mês exibido
$sqlBloqueios = "SELECT id_bloqueio, data_inicio, data_fim, motivo
                 FROM bloqueios
                 WHERE ativo = 1
                   AND data_inicio <= ?
                   AND data_fim    >= ?";

$stmtBl = $conn->prepare($sqlBloqueios);
$stmtBl->bind_param("ss", $fimMes, $inicioMes);
$stmtBl->execute();
$resultBl = $stmtBl->get_result();

$bloqueiosPorDia = [];
while ($bl = $resultBl->fetch_assoc()) {
    $cursor = strtotime($bl['data_inicio']);
    $fim    = strtotime($bl['data_fim']);
    while ($cursor <= $fim) {
        $dataCursor = date('Y-m-d', $cursor);
        if ($dataCursor >= $inicioMes && $dataCursor <= $fimMes) {
            $bloqueiosPorDia[$dataCursor][] = [
                'id_bloqueio' => $bl['id_bloqueio'],
                'data_inicio' => $bl['data_inicio'],
                'data_fim'    => $bl['data_fim'],
                'motivo'      => $bl['motivo'],
            ];
        }
        $cursor = strtotime('+1 day', $cursor);
    }
}
$stmtBl->close();

$hoje = date('Y-m-d');
?>

<div class="painel-container">

    <?php include '../../includes/layout/sidebar.php'; ?>
    <div class="sidebar-overlay"></div>

    <main class="conteudo">
        <header class="painel-header">
            <button class="menu-toggle">
                <i class="fa-solid fa-bars"></i>
            </button>
        </header>

        <!-- barra de pesquisa -->
        <div class="pesquisa-barra">
            <div class="pesquisa-input-wrap">
                <i class="fa-solid fa-magnifying-glass pesquisa-icone"></i>
                <input type="text" id="pesquisaNome" placeholder="Pesquisar reserva por nome..." autocomplete="off">
            </div>
            <div id="pesquisaResultados" class="pesquisa-resultados"></div>
        </div>

        <!-- calendario -->
        <div class="calendar-container">

            <div class="calendar-header">

                <a href="?mes=<?= $mesAnterior ?>&ano=<?= $anoAnterior ?>" class="cal-nav">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>

                <form method="GET" class="cal-selector">
                    <div class="select-wrapper">
                        <select name="mes" onchange="this.form.submit()">
                            <?php foreach ($nomesMes as $num => $nome): ?>
                                <option value="<?= $num ?>" <?= $num == $mesAtual ? 'selected' : '' ?>>
                                    <?= trim($nome) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="select-wrapper">
                        <select name="ano" onchange="this.form.submit()">
                            <?php
                            $anoInicio = $anoAtual - 5;
                            $anoFim = $anoAtual + 5;
                            for ($ano = $anoInicio; $ano <= $anoFim; $ano++): ?>
                                <option value="<?= $ano ?>" <?= $ano == $anoAtual ? 'selected' : '' ?>>
                                    <?= $ano ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </form>

                <a href="?mes=<?= $mesProximo ?>&ano=<?= $anoProximo ?>" class="cal-nav">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>

            </div>

            <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
            <div class="bloqueio-toolbar">
                <button type="button" id="btnBloqueioMode" class="btn-bloquear-periodo">
                    <i class="fa-solid fa-calendar-xmark"></i>
                    Bloquear período
                </button>
                <span id="bloqueioInstrucao" class="bloqueio-instrucao"></span>
                <button type="button" id="btnBloqueioConfirmar" class="btn-bloqueio-confirmar">
                    <i class="fa-solid fa-check"></i>
                    Confirmar
                </button>
                <button type="button" id="btnBloqueioCancelar" class="btn-bloqueio-cancelar">
                    <i class="fa-solid fa-xmark"></i>
                    Cancelar
                </button>
            </div>
            <?php endif; ?>

            <div class="calendar-grid">
                <div class="cal-weekday">Dom</div>
                <div class="cal-weekday">Seg</div>
                <div class="cal-weekday">Ter</div>
                <div class="cal-weekday">Qua</div>
                <div class="cal-weekday">Qui</div>
                <div class="cal-weekday">Sex</div>
                <div class="cal-weekday">Sáb</div>

                <?php for ($i = 0; $i < $diaSemanaPrimeiro; $i++): ?>
                    <div class="cal-dia vazio"></div>
                <?php endfor; ?>

                <?php
                for ($dia = 1; $dia <= $qtdeDiasMes; $dia++):
                    $dataDia      = sprintf('%04d-%02d-%02d', $anoAtual, $mesAtual, $dia);
                    $temReservas  = isset($reservasPorDia[$dataDia]);
                    $temBloqueio  = isset($bloqueiosPorDia[$dataDia]);
                    $reservasDoDia = $temReservas ? $reservasPorDia[$dataDia] : [];
                    $bloqueiosDoDia = $temBloqueio ? $bloqueiosPorDia[$dataDia] : [];
                    $qtdReservas  = count($reservasDoDia);
                    $isHoje       = ($dataDia === $hoje);

                    $classes = ['cal-dia'];
                    if ($temBloqueio)   $classes[] = 'bloqueado';
                    elseif ($temReservas) $classes[] = 'reservado';
                    else                $classes[] = 'livre';
                    if ($isHoje)        $classes[] = 'hoje';

                    $dataAttrs = '';
                    if ($temReservas) {
                        $dataAttrs .= ' data-has-reserva="1"';
                        $dataAttrs .= ' data-reservas-count="' . $qtdReservas . '"';
                        $dataAttrs .= ' data-first-id="' . $reservasDoDia[0]['id_reserva'] . '"';
                        $dataAttrs .= ' data-reservas="' . htmlspecialchars(json_encode($reservasDoDia), ENT_QUOTES, 'UTF-8') . '"';
                    } else {
                        $dataAttrs .= ' data-has-reserva="0"';
                    }
                    if ($temBloqueio) {
                        $dataAttrs .= ' data-bloqueios="' . htmlspecialchars(json_encode($bloqueiosDoDia), ENT_QUOTES, 'UTF-8') . '"';
                    }
                    ?>
                    <button type="button"
                        class="<?= implode(' ', $classes) ?>"
                        data-date="<?= $dataDia ?>"
                        <?= $dataAttrs ?>>
                        <span class="cal-dia-num"><?= $dia ?></span>

                        <?php if ($temBloqueio): ?>
                            <span class="cal-dia-info bloqueado-icone">
                                <i class="fa-solid fa-ban"></i>
                            </span>
                        <?php elseif ($qtdReservas >= 2): ?>
                            <span class="cal-dia-info"><?= $qtdReservas ?></span>
                        <?php endif; ?>
                    </button>
                <?php endfor; ?>
            </div>
        </div>
    </main>
</div>

<!-- Modal de reservas do dia -->
<div id="calendarModal" class="popup-modal">
    <div class="popup-box">
        <h2>
            Reservas do dia <span id="calModalData"></span>
        </h2>
        <div id="calModalFeriado"></div>
        <div id="calModalClima"></div>
        <div id="calModalLista"></div>
        <div class="popup-buttons">
            <button id="fecharCalModal" class="btn btn-fechar">Fechar</button>
            <a href="#" id="btnNovaReservaDia" class="btn btn-novo">
                <i class="fa-solid fa-plus"></i>
                Nova reserva
            </a>
        </div>
    </div>
</div>

<!-- Modal de bloqueio -->
<div id="bloqueioModal" class="popup-modal">
    <div class="popup-box">
        <h2>
            <i class="fa-solid fa-ban"></i>
            Data Bloqueada<br><span id="bloqueioModalData"></span>
        </h2>
        <div id="bloqueioModalLista"></div>
        <div class="popup-buttons">
            <button id="fecharBloqueioModal" class="btn btn-fechar">Fechar</button>
        </div>
    </div>
</div>

<!-- Modal: confirmar novo bloqueio pelo calendário -->
<div id="novoBloqueioModal" class="popup-modal">
    <div class="popup-box">
        <h2><i class="fa-solid fa-calendar-xmark"></i> Novo Bloqueio</h2>
        <p id="novoBloqueioRange" style="font-weight:600;font-size:15px;color:#d97706;margin-bottom:4px;"></p>
        <div class="form-cadastro" style="margin-top:12px;">
            <div class="form-grupo">
                <label>Motivo *</label>
                <textarea id="novoBloqueioMotivo" rows="3"
                    placeholder="Ex: Manutenção, evento particular..."></textarea>
            </div>
        </div>
        <p id="novoBloqueioErro" style="display:none;color:#d53625;font-size:13px;margin-top:8px;text-align:left;"></p>
        <div class="form-botoes" style="justify-content:center;margin-top:20px;">
            <button id="novoBloqueioSalvar" class="btn btn-salvar">
                <i class="fa-solid fa-floppy-disk"></i>
                Salvar
            </button>
            <button id="novoBloqueioFechar" class="btn btn-fechar">Cancelar</button>
        </div>
    </div>
</div>

<script>const CSRF_TOKEN = '<?= csrf_token() ?>';</script>

<script>
(function () {
    const input      = document.getElementById('pesquisaNome');
    const resultados = document.getElementById('pesquisaResultados');

    // --- busca com debounce ---
    let timer = null;

    input.addEventListener('input', () => {
        clearTimeout(timer);
        const q = input.value.trim();

        if (q.length < 2) {
            if (q.length === 0) {
                resultados.innerHTML = '';
                resultados.classList.remove('active');
            } else {
                resultados.innerHTML = '<p class="pesquisa-vazio">Digite ao menos 2 caracteres.</p>';
                resultados.classList.add('active');
            }
            return;
        }

        resultados.innerHTML = '<p class="pesquisa-vazio">Buscando...</p>';
        resultados.classList.add('active');

        timer = setTimeout(async () => {
            try {
                const res  = await fetch('buscar_ajax.php?q=' + encodeURIComponent(q));
                const data = await res.json();
                renderResultados(data, q);
            } catch {
                resultados.innerHTML = '<p class="pesquisa-vazio">Erro ao buscar. Tente novamente.</p>';
                resultados.classList.add('active');
            }
        }, 300);
    });

    function fmtData(iso) {
        const [y, m, d] = iso.split('-');
        return `${d}/${m}/${y}`;
    }

    function highlight(text, q) {
        const palavras = q.trim().split(/\s+/).filter(Boolean);
        const pattern  = palavras.map(p => p.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|');
        const regex    = new RegExp('(' + pattern + ')', 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }

    function renderResultados(data, q) {
        if (data.length === 0) {
            resultados.innerHTML = '<p class="pesquisa-vazio">Nenhuma reserva encontrada.</p>';
            resultados.classList.add('active');
            return;
        }

        const html = data.map(r => `
            <a href="edit.php?id=${r.id_reserva}" class="pesquisa-item">
                <span class="pesquisa-nome">${highlight(r.nome_reserva, q)}</span>
                <span class="pesquisa-meta">
                    <i class="fa-solid fa-calendar-day"></i> ${fmtData(r.data_reserva)}
                    &nbsp;·&nbsp;
                    <i class="fa-solid fa-clock"></i> ${r.hora_inicio.slice(0,5)}–${r.hora_fim.slice(0,5)}
                    ${r.nome_ambiente ? `&nbsp;·&nbsp;<i class="fa-solid fa-location-dot"></i> ${r.nome_ambiente}` : ''}
                </span>
            </a>
        `).join('');

        resultados.innerHTML = `<p class="pesquisa-count">${data.length} resultado${data.length !== 1 ? 's' : ''}</p>` + html;
        resultados.classList.add('active');
    }
}());
</script>

<?php include '../../includes/layout/footer.php'; ?>