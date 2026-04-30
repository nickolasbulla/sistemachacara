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
    const deleteIdInput = document.getElementById("deleteId");

    if (btnsDelete.length && modalDelete && cancelDelete && deleteIdInput) {

        btnsDelete.forEach(btn => {
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                deleteIdInput.value = btn.dataset.id;
                modalDelete.classList.add("active");
            });
        });

        cancelDelete.addEventListener("click", () => {
            modalDelete.classList.remove("active");
        });
    }
});