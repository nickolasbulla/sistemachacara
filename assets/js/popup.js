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
});