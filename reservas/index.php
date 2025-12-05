<?php
session_start();
include '../includes/login_verify.php';
include '../includes/db.php';

$titulo_pagina = "Reservas - Chácara Portal";
$css_pagina = ["../assets/css/painel.css", "../assets/css/crud.css", "../assets/css/calendar.css"];
include '../includes/header.php';

//  calculo do mes / ano atual
$mesAtual = isset($_GET['mes']) ? (int) $_GET['mes'] : (int) date('m');
$anoAtual = isset($_GET['ano']) ? (int) $_GET['ano'] : (int) date('Y');

// ajusta mês fora do range 1-12
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
$qtdeDiasMes = (int) date('t', $primeiroDiaMes); // quantos dias tem o mês
$diaSemanaPrimeiro = (int) date('w', $primeiroDiaMes); // 0 = domingo ... 6 = sábado

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

//  busca das reservas do mes

$inicioMes = date('Y-m-01', $primeiroDiaMes);
$fimMes = date('Y-m-t', $primeiroDiaMes);

$sql = "SELECT id_reserva, nome_reserva, data_reserva, hora_inicio, hora_fim, valor_cobrado, valor_pago
        FROM reservas
        WHERE data_reserva BETWEEN ? AND ?
        ORDER BY data_reserva, hora_inicio";

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
        'id_reserva' => $row['id_reserva'],
        'nome_reserva' => $row['nome_reserva'],
        'hora_inicio' => substr($row['hora_inicio'], 0, 5),
        'hora_fim' => substr($row['hora_fim'], 0, 5),
        'valor_cobrado' => (float) $row['valor_cobrado'],
        'valor_pago' => (float) $row['valor_pago'],
        'falta' => (float) $row['valor_cobrado'] - (float) $row['valor_pago'],
    ];
}

$stmt->close();

$hoje = date('Y-m-d');
?>

<div class="painel-container">

    <?php include '../includes/sidebar.php'; ?>
    <div class="sidebar-overlay"></div>

    <main class="conteudo">
        <header class="painel-header">
            <button class="menu-toggle">☰</button>
            <h1>Reservas</h1>
        </header>

        <?php if (isset($_GET['deletado']) && $_GET['deletado'] == 1): ?>
            <div class="alerta sucesso">Reserva excluída com sucesso!</div>
        <?php endif; ?>

        <!-- calendario -->
        <div class="calendar-container">

            <div class="calendar-header">
                <a href="?mes=<?= $mesAnterior ?>&ano=<?= $anoAnterior ?>" class="cal-nav">&laquo;</a>
                <div class="cal-current"><?= $nomeMesAtual . ' ' . $anoAtual ?></div>
                <a href="?mes=<?= $mesProximo ?>&ano=<?= $anoProximo ?>" class="cal-nav">&raquo;</a>
            </div>

            <div class="calendar-legend">
                <span class="legenda-item">
                    <span class="legenda-cor reservado"></span> Reservado
                </span>
                <span class="legenda-item">
                    <span class="legenda-cor livre"></span> Livre
                </span>
                <span class="legenda-item">
                    <span class="legenda-cor hoje"></span> Hoje
                </span>
            </div>

            <div class="calendar-grid">
                <div class="cal-weekday">Dom</div>
                <div class="cal-weekday">Seg</div>
                <div class="cal-weekday">Ter</div>
                <div class="cal-weekday">Qua</div>
                <div class="cal-weekday">Qui</div>
                <div class="cal-weekday">Sex</div>
                <div class="cal-weekday">Sáb</div>

                <?php
                // espaços em branco antes do dia 1 se o mês não começa no domingo
                for ($i = 0; $i < $diaSemanaPrimeiro; $i++): ?>
                    <div class="cal-dia vazio"></div>
                <?php endfor; ?>

                <?php
                //dias do mês
                for ($dia = 1; $dia <= $qtdeDiasMes; $dia++):
                    $dataDia = sprintf('%04d-%02d-%02d', $anoAtual, $mesAtual, $dia);
                    $temReservas = isset($reservasPorDia[$dataDia]);
                    $reservasDoDia = $temReservas ? $reservasPorDia[$dataDia] : [];
                    $qtdReservas = $temReservas ? count($reservasDoDia) : 0;
                    $isHoje = ($dataDia === $hoje);

                    // dados para js
                    $dataAttrs = '';
                    if ($temReservas) {
                        $dataAttrs .= ' data-has-reserva="1"';
                        $dataAttrs .= ' data-reservas-count="' . $qtdReservas . '"';
                        $dataAttrs .= ' data-first-id="' . $reservasDoDia[0]['id_reserva'] . '"';
                        $dataAttrs .= ' data-reservas="' . htmlspecialchars(json_encode($reservasDoDia), ENT_QUOTES, 'UTF-8') . '"';
                    } else {
                        $dataAttrs .= ' data-has-reserva="0"';
                    }
                    ?>
                    <button type="button"
                        class="cal-dia <?= $temReservas ? 'reservado' : 'livre' ?> <?= $isHoje ? 'hoje' : '' ?>"
                        data-date="<?= $dataDia ?>" <?= $dataAttrs ?>>
                        <span class="cal-dia-num"><?= $dia ?></span>

                        <?php if ($temReservas): ?>
                            <span class="cal-dia-info">
                                <?= $qtdReservas === 1 ? '1 reserva' : $qtdReservas . ' reservas' ?>
                            </span>
                        <?php endif; ?>
                    </button>
                <?php endfor; ?>
            </div>
        </div>
    </main>
</div>

<!-- popup do calendário para dias com mais de uma reserva -->
<div id="calendarModal" class="popup-modal">
    <div class="popup-box">
        <h2>Reservas do dia <span id="calModalData"></span></h2>
        <div id="calModalLista"></div>
        <div class="popup-buttons">
            <button id="fecharCalModal" class="btn btn-cancelar">Fechar</button>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>