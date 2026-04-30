<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/db.php';

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
            Data Bloqueada<span id="bloqueioModalData"></span>
        </h2>
        <div id="bloqueioModalLista"></div>
        <div class="popup-buttons">
            <button id="fecharBloqueioModal" class="btn btn-fechar">Fechar</button>
        </div>
    </div>
</div>

<?php include '../../includes/layout/footer.php'; ?>