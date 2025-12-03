document.addEventListener("DOMContentLoaded", () => {

    /* logout */
    const btnLogout = document.getElementById("btnLogout");
    const modalLogout = document.getElementById("logoutModal");
    const cancelLogout = document.getElementById("cancelLogout");

    if (btnLogout && modalLogout && cancelLogout) {

        btnLogout.addEventListener("click", (e) => {
            e.preventDefault();
            modalLogout.classList.add("active");
        });

        cancelLogout.addEventListener("click", () => {
            modalLogout.classList.remove("active");
        });
    }


    /* delete */
    const btnsDelete = document.querySelectorAll(".btnpopup");
    const modalDelete = document.getElementById("deleteModal");
    const cancelDelete = document.getElementById("cancelDelete");
    const confirmDelete = document.getElementById("confirmDelete");

    if (btnsDelete.length && modalDelete && cancelDelete && confirmDelete) {

        btnsDelete.forEach(btn => {
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                modalDelete.classList.add("active");
                confirmDelete.dataset.id = btn.dataset.id;
            });
        });

        cancelDelete.addEventListener("click", () => {
            modalDelete.classList.remove("active");
        });

        confirmDelete.addEventListener("click", (e) => {
            e.preventDefault();

            const id = confirmDelete.dataset.id;

            // pega o nome do arquivo atual
            const currentPage = window.location.pathname.split('/').pop();

            window.location.href = `${currentPage}?delete_id=${id}&id=${id}`;
        });
    }

    /* relatorios */
    const btnsRelatorio = document.querySelectorAll(".btnRelatorio");
    const modal = document.getElementById("relatorioModal");
    const cancel = document.getElementById("cancelRelatorio");
    const titulo = document.getElementById("tituloRelatorio");
    const campoPeriodo = document.getElementById("campoPeriodo");
    const campoUsuario = document.getElementById("campoUsuario");
    const campoAmbiente = document.getElementById("campoAmbiente");
    const form = document.getElementById("formRelatorio");

    let tipoAtual = "";

    btnsRelatorio.forEach(btn => {
        btn.addEventListener("click", e => {
            e.preventDefault();

            tipoAtual = btn.dataset.relatorio;

            modal.classList.add("active");

            // esconde tudo
            campoPeriodo.style.display = "none";
            campoUsuario.style.display = "none";
            campoAmbiente.style.display = "none";

            //limpa required
            campoPeriodo.querySelectorAll("input").forEach(i => i.required = false);
            if (campoUsuario.querySelector("select")) {
                campoUsuario.querySelector("select").required = false;
            }   
            if (campoAmbiente.querySelector("select")) {
                campoAmbiente.querySelector("select").required = false;
            }

            // configurações por relatório
            if (tipoAtual === "nao_pagas") {
                titulo.textContent = "Reservas Não Pagas";
                form.action = "nao_pagas.php";
                campoPeriodo.style.display = "block";
                campoPeriodo.querySelectorAll("input").forEach(i => i.required = true);
            }

            if (tipoAtual === "por_usuario") {
                titulo.textContent = "Reservas por Usuário";
                form.action = "por_usuario.php";
                campoUsuario.style.display = "block";
                campoUsuario.querySelector("select").required = true;
            }

            if (tipoAtual === "por_ambiente") {
                titulo.textContent = "Reservas por Ambiente";
                form.action = "por_ambiente.php";
                campoPeriodo.style.display = "block";
                campoAmbiente.style.display = "block"; // novo campo

                campoPeriodo.querySelectorAll("input").forEach(i => i.required = true);
                campoAmbiente.querySelector("select").required = true;
            }
        });
    });

    cancel.addEventListener("click", () => {
        modal.classList.remove("active");
    });

    /* validacao antes de enviar */
    form.addEventListener("submit", (e) => {

        if (tipoAtual === "nao_pagas" || tipoAtual === "por_ambiente") {
            const inicio = form.querySelector("input[name='inicio']").value;
            const fim = form.querySelector("input[name='fim']").value;

            if (!inicio || !fim) {
                e.preventDefault();
                alert("Preencha as datas do período.");
                return;
            }
        }

        if (tipoAtual === "por_usuario") {
            const user = form.querySelector("select[name='id_usuario']").value;

            if (!user) {
                e.preventDefault();
                alert("Selecione um usuário.");
                return;
            }
        }
    });
});