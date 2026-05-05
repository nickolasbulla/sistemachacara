<?php
session_start();

include '../../includes/auth/login_verify.php';
include '../../config/init.php';

$usuarios_lista = $conn->query("SELECT id_usuario, nome_completo FROM usuarios WHERE ativo = 1 ORDER BY nome_completo ASC")->fetch_all(MYSQLI_ASSOC);
$ambientes_lista = $conn->query("SELECT id_ambiente, nome_ambiente FROM ambientes WHERE ativo = 1 ORDER BY nome_ambiente ASC")->fetch_all(MYSQLI_ASSOC);

$titulo_pagina = "Relatórios - Chácara Portal";
$body_class = "painel-page";
include "../../includes/layout/header.php";
?>

<div class="painel-container">

    <?php include '../../includes/layout/sidebar.php'; ?>
    <div class="sidebar-overlay"></div>

    <main class="conteudo">

        <header class="painel-header">
            <button class="menu-toggle">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1>Relatórios</h1>
        </header>

        <div class="relatorios-grid">

            <div class="relatorio-card">
                <h2>Reservas por usuário</h2>
                <p>Lista todas as reservas feitas por um usuário específico.</p>
                <button class="btn-relatorio btnRelatorio" data-relatorio="por_usuario">
                    <i class="fa-solid fa-download"></i> Gerar PDF
                </button>
            </div>

            <div class="relatorio-card">
                <h2>Reservas por ambiente</h2>
                <p>Lista as reservas de um ambiente específico ou de todos dentro de um período.</p>
                <button class="btn-relatorio btnRelatorio" data-relatorio="por_ambiente">
                    <i class="fa-solid fa-download"></i> Gerar PDF
                </button>
            </div>

            <div class="relatorio-card">
                <h2>Reservas sem funcionário</h2>
                <p>Lista reservas do período que não têm funcionário atribuído.</p>
                <button class="btn-relatorio btnRelatorio" data-relatorio="sem_funcionario">
                    <i class="fa-solid fa-download"></i> Gerar PDF
                </button>
            </div>

            <div class="relatorio-card">
                <h2>Faturamento</h2>
                <p>Exibe o faturamento total, recebido e em aberto de todas as reservas dentro de um período.</p>
                <button class="btn-relatorio btnRelatorio" data-relatorio="faturamento">
                    <i class="fa-solid fa-download"></i> Gerar PDF
                </button>
            </div>

            <div class="relatorio-card">
                <h2>Ocorrências</h2>
                <p>Lista ocorrências registradas em vistorias dentro de um período.</p>
                <button class="btn-relatorio btnRelatorio" data-relatorio="ocorrencias">
                    <i class="fa-solid fa-download"></i> Gerar PDF
                </button>
            </div>

            <div class="relatorio-card">
                <h2>Datas bloqueadas</h2>
                <p>Lista todos os bloqueios cadastrados dentro de um período.</p>
                <button class="btn-relatorio btnRelatorio" data-relatorio="bloqueios">
                    <i class="fa-solid fa-download"></i> Gerar PDF
                </button>
            </div>

        </div>

    </main>
</div>

<!-- Modal: Reservas por ambiente -->
<div id="modalPorAmbiente" class="popup-modal">
    <div class="popup-box relatorio-modal-box">
        <h2>Reservas por ambiente</h2>
        <p>Selecione o ambiente e o período para o relatório</p>

        <form id="formPorAmbiente" action="gerar.php" method="GET">
            <input type="hidden" name="tipo" value="por_ambiente">

            <div class="form-grupo" style="margin-bottom: 14px;">
                <label for="pa_id_ambiente">Ambiente</label>
                <div class="select-wrapper">
                    <select id="pa_id_ambiente" name="id_ambiente">
                        <option value="">Todos</option>
                        <?php foreach ($ambientes_lista as $a): ?>
                            <option value="<?= $a['id_ambiente'] ?>"><?= htmlspecialchars($a['nome_ambiente']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="relatorio-modal-campos">
                <div class="form-grupo">
                    <label for="pa_data_inicio">Data início</label>
                    <input type="text" class="input-data" id="pa_data_inicio" name="data_inicio"
                        placeholder="DD/MM/AAAA" required>
                </div>
                <div class="form-grupo">
                    <label for="pa_data_fim">Data fim</label>
                    <input type="text" class="input-data" id="pa_data_fim" name="data_fim" placeholder="DD/MM/AAAA"
                        required>
                </div>
            </div>

            <div class="popup-buttons" style="margin-top: 24px;">
                <button type="submit" class="btn-confirmar">
                    <i class="fa-solid fa-download"></i> Gerar PDF
                </button>
                <button type="button" class="btn-cancelar" id="btnFecharModalPorAmbiente">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Faturamento -->
<div id="modalFaturamento" class="popup-modal">
    <div class="popup-box relatorio-modal-box">
        <h2>Faturamento</h2>
        <p>Selecione o período para o relatório</p>

        <form id="formFaturamento" action="gerar.php" method="GET">
            <input type="hidden" name="tipo" value="faturamento">

            <div class="relatorio-modal-campos">
                <div class="form-grupo">
                    <label for="fat_data_inicio">Data início</label>
                    <input type="text" class="input-data" id="fat_data_inicio" name="data_inicio"
                        placeholder="DD/MM/AAAA" required>
                </div>
                <div class="form-grupo">
                    <label for="fat_data_fim">Data fim</label>
                    <input type="text" class="input-data" id="fat_data_fim" name="data_fim" placeholder="DD/MM/AAAA"
                        required>
                </div>
            </div>

            <div class="popup-buttons" style="margin-top: 24px;">
                <button type="submit" class="btn-confirmar">
                    <i class="fa-solid fa-download"></i> Gerar PDF
                </button>
                <button type="button" class="btn-cancelar" id="btnFecharModalFaturamento">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Reservas por usuário -->
<div id="modalPorUsuario" class="popup-modal">
    <div class="popup-box relatorio-modal-box">
        <h2>Reservas por usuário</h2>
        <p>Selecione o usuário e o período para o relatório</p>

        <form id="formPorUsuario" action="gerar.php" method="GET">
            <input type="hidden" name="tipo" value="por_usuario">

            <div class="form-grupo" style="margin-bottom: 14px;">
                <label for="id_usuario">Usuário</label>
                <div class="select-wrapper">
                    <select id="id_usuario" name="id_usuario" required>
                        <option value="">Selecione</option>
                        <?php foreach ($usuarios_lista as $u): ?>
                            <option value="<?= $u['id_usuario'] ?>"><?= htmlspecialchars($u['nome_completo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="relatorio-modal-campos">
                <div class="form-grupo">
                    <label for="pu_data_inicio">Data início</label>
                    <input type="text" class="input-data" id="pu_data_inicio" name="data_inicio"
                        placeholder="DD/MM/AAAA" required>
                </div>
                <div class="form-grupo">
                    <label for="pu_data_fim">Data fim</label>
                    <input type="text" class="input-data" id="pu_data_fim" name="data_fim" placeholder="DD/MM/AAAA"
                        required>
                </div>
            </div>

            <div class="popup-buttons" style="margin-top: 24px;">
                <button type="submit" class="btn-confirmar">
                    <i class="fa-solid fa-download"></i> Gerar PDF
                </button>
                <button type="button" class="btn-cancelar" id="btnFecharModalPorUsuario">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Ocorrências -->
<div id="modalOcorrencias" class="popup-modal">
    <div class="popup-box relatorio-modal-box">
        <h2>Ocorrências</h2>
        <p>Selecione o período e o status para o relatório</p>

        <form id="formOcorrencias" action="gerar.php" method="GET">
            <input type="hidden" name="tipo" value="ocorrencias">

            <div class="form-grupo" style="margin-bottom: 14px;">
                <label for="oc_status">Status</label>
                <div class="select-wrapper">
                    <select id="oc_status" name="status">
                        <option value="">Todos</option>
                        <option value="aberta">Abertas</option>
                        <option value="resolvida">Resolvidas</option>
                    </select>
                </div>
            </div>

            <div class="relatorio-modal-campos">
                <div class="form-grupo">
                    <label for="oc_data_inicio">Data início</label>
                    <input type="text" class="input-data" id="oc_data_inicio" name="data_inicio"
                        placeholder="DD/MM/AAAA" required>
                </div>
                <div class="form-grupo">
                    <label for="oc_data_fim">Data fim</label>
                    <input type="text" class="input-data" id="oc_data_fim" name="data_fim" placeholder="DD/MM/AAAA"
                        required>
                </div>
            </div>

            <div class="popup-buttons" style="margin-top: 24px;">
                <button type="submit" class="btn-confirmar">
                    <i class="fa-solid fa-download"></i> Gerar PDF
                </button>
                <button type="button" class="btn-cancelar" id="btnFecharModalOcorrencias">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Sem funcionário -->
<div id="modalSemFuncionario" class="popup-modal">
    <div class="popup-box relatorio-modal-box">
        <h2>Sem funcionário</h2>
        <p>Selecione o período para o relatório</p>

        <form id="formSemFuncionario" action="gerar.php" method="GET">
            <input type="hidden" name="tipo" value="sem_funcionario">

            <div class="relatorio-modal-campos">
                <div class="form-grupo">
                    <label for="sf_data_inicio">Data início</label>
                    <input type="text" class="input-data" id="sf_data_inicio" name="data_inicio"
                        placeholder="DD/MM/AAAA" required>
                </div>
                <div class="form-grupo">
                    <label for="sf_data_fim">Data fim</label>
                    <input type="text" class="input-data" id="sf_data_fim" name="data_fim" placeholder="DD/MM/AAAA"
                        required>
                </div>
            </div>

            <div class="popup-buttons" style="margin-top: 24px;">
                <button type="submit" class="btn-confirmar">
                    <i class="fa-solid fa-download"></i> Gerar PDF
                </button>
                <button type="button" class="btn-cancelar" id="btnFecharModalSemFuncionario">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Datas bloqueadas -->
<div id="modalBloqueios" class="popup-modal">
    <div class="popup-box relatorio-modal-box">
        <h2>Datas bloqueadas</h2>
        <p>Selecione o período e o status para o relatório</p>

        <form id="formBloqueios" action="gerar.php" method="GET">
            <input type="hidden" name="tipo" value="bloqueios">

            <div class="form-grupo" style="margin-bottom: 14px;">
                <label for="bl_status">Status</label>
                <div class="select-wrapper">
                    <select id="bl_status" name="status">
                        <option value="">Todos</option>
                        <option value="1">Apenas ativos</option>
                        <option value="0">Apenas inativos</option>
                    </select>
                </div>
            </div>

            <div class="relatorio-modal-campos">
                <div class="form-grupo">
                    <label for="bl_data_inicio">Data início</label>
                    <input type="text" class="input-data" id="bl_data_inicio" name="data_inicio"
                        placeholder="DD/MM/AAAA" required>
                </div>
                <div class="form-grupo">
                    <label for="bl_data_fim">Data fim</label>
                    <input type="text" class="input-data" id="bl_data_fim" name="data_fim" placeholder="DD/MM/AAAA"
                        required>
                </div>
            </div>

            <div class="popup-buttons" style="margin-top: 24px;">
                <button type="submit" class="btn-confirmar">
                    <i class="fa-solid fa-download"></i> Gerar PDF
                </button>
                <button type="button" class="btn-cancelar" id="btnFecharModalBloqueios">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modais = {
        faturamento: document.getElementById('modalFaturamento'),
        por_usuario: document.getElementById('modalPorUsuario'),
        ocorrencias: document.getElementById('modalOcorrencias'),
        bloqueios: document.getElementById('modalBloqueios'),
        por_ambiente: document.getElementById('modalPorAmbiente'),
        sem_funcionario: document.getElementById('modalSemFuncionario'),
    };

    document.querySelectorAll('.btnRelatorio').forEach(btn => {
        btn.addEventListener('click', () => {
            const tipo = btn.dataset.relatorio;
            if (modais[tipo]) modais[tipo].classList.add('active');
        });
    });

    function parseDataBR(s) {
        const [d, m, y] = s.split('/');
        return new Date(y, m - 1, d);
    }

    function abrirPdf(form, modalId) {
        const campoInicio = form.querySelector('[name="data_inicio"]');
        const campoFim    = form.querySelector('[name="data_fim"]');

        if (campoInicio?.value && campoFim?.value) {
            if (parseDataBR(campoFim.value) < parseDataBR(campoInicio.value)) {
                let erroDiv = form.querySelector('.modal-erro-data');
                if (!erroDiv) {
                    erroDiv = document.createElement('div');
                    erroDiv.className = 'alerta erro modal-erro-data';
                    form.querySelector('.popup-buttons').before(erroDiv);
                }
                erroDiv.textContent = 'A data fim não pode ser anterior à data início.';
                return;
            }
        }

        form.querySelector('.modal-erro-data')?.remove();
        const url = 'gerar.php?' + new URLSearchParams(new FormData(form));
        window.location.href = url;
        document.getElementById(modalId).classList.remove('active');
    }

    document.getElementById('btnFecharModalPorUsuario').addEventListener('click', () => {
        document.getElementById('modalPorUsuario').classList.remove('active');
    });
    document.getElementById('modalPorUsuario').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) e.currentTarget.classList.remove('active');
    });
    document.getElementById('formPorUsuario').addEventListener('submit', (e) => {
        e.preventDefault();
        abrirPdf(e.target, 'modalPorUsuario');
    });

    document.getElementById('btnFecharModalOcorrencias').addEventListener('click', () => {
        document.getElementById('modalOcorrencias').classList.remove('active');
    });
    document.getElementById('modalOcorrencias').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) e.currentTarget.classList.remove('active');
    });
    document.getElementById('formOcorrencias').addEventListener('submit', (e) => {
        e.preventDefault();
        abrirPdf(e.target, 'modalOcorrencias');
    });

    document.getElementById('btnFecharModalBloqueios').addEventListener('click', () => {
        document.getElementById('modalBloqueios').classList.remove('active');
    });
    document.getElementById('modalBloqueios').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) e.currentTarget.classList.remove('active');
    });
    document.getElementById('formBloqueios').addEventListener('submit', (e) => {
        e.preventDefault();
        abrirPdf(e.target, 'modalBloqueios');
    });

    document.getElementById('btnFecharModalFaturamento').addEventListener('click', () => {
        document.getElementById('modalFaturamento').classList.remove('active');
    });
    document.getElementById('modalFaturamento').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) e.currentTarget.classList.remove('active');
    });
    document.getElementById('formFaturamento').addEventListener('submit', (e) => {
        e.preventDefault();
        abrirPdf(e.target, 'modalFaturamento');
    });

    document.getElementById('btnFecharModalPorAmbiente').addEventListener('click', () => {
        document.getElementById('modalPorAmbiente').classList.remove('active');
    });
    document.getElementById('modalPorAmbiente').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) e.currentTarget.classList.remove('active');
    });
    document.getElementById('formPorAmbiente').addEventListener('submit', (e) => {
        e.preventDefault();
        abrirPdf(e.target, 'modalPorAmbiente');
    });

    document.getElementById('btnFecharModalSemFuncionario').addEventListener('click', () => {
        document.getElementById('modalSemFuncionario').classList.remove('active');
    });
    document.getElementById('modalSemFuncionario').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) e.currentTarget.classList.remove('active');
    });
    document.getElementById('formSemFuncionario').addEventListener('submit', (e) => {
        e.preventDefault();
        abrirPdf(e.target, 'modalSemFuncionario');
    });
</script>

<?php include '../../includes/layout/footer.php'; ?>