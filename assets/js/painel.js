document.addEventListener("DOMContentLoaded", () => {

    // SUBMENU SIDEBAR
    const toggles = document.querySelectorAll(".submenu-toggle");

    toggles.forEach(toggle => {
        toggle.addEventListener("click", () => {
            const submenu = toggle.parentElement;
            submenu.classList.toggle("open");
        });
    });

    // ABRIR / FECHAR SIDEBAR
    const menuToggle = document.querySelector(".menu-toggle");
    const sidebar = document.querySelector(".sidebar");
    const overlay = document.querySelector(".sidebar-overlay");

    if (!menuToggle || !sidebar || !overlay) return;

    const toggleSidebar = () => {
        sidebar.classList.toggle("active");
        overlay.classList.toggle("active");
    };

    const closeSidebar = () => {
        sidebar.classList.remove("active");
        overlay.classList.remove("active");
    };

    menuToggle.addEventListener("click", toggleSidebar);
    overlay.addEventListener("click", closeSidebar);

});