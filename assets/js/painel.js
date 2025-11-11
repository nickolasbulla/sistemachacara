// sub-menu do painel lateral
document.addEventListener("DOMContentLoaded", function() {
    const toggles = document.querySelectorAll(".submenu-toggle");

    toggles.forEach(toggle => {
        toggle.addEventListener("click", function() {
            const submenu = this.parentElement;
            submenu.classList.toggle("open");
        });
    });
});

// pop up do logout
document.addEventListener("DOMContentLoaded", () => {
    const btnPopup = document.getElementById("btnPopup");
    const modal = document.getElementById("popupModal");
    const cancel = document.getElementById("cancelPopup");

    btnPopup.addEventListener("click", (e) => {
        e.preventDefault();
        modal.classList.add("active");
    });

    cancel.addEventListener("click", () => {
        modal.classList.remove("active");
    });
});

// Botão de abrir/fechar menu
document.addEventListener("DOMContentLoaded", () => {
  const menuToggle = document.querySelector(".menu-toggle");
  const sidebar = document.querySelector(".sidebar");
  const overlay = document.querySelector(".sidebar-overlay");

  if (!menuToggle || !sidebar || !overlay) return; // segurança

  menuToggle.addEventListener("click", () => {
    sidebar.classList.toggle("active");
    overlay.classList.toggle("active");
  });

  overlay.addEventListener("click", () => {
    sidebar.classList.remove("active");
    overlay.classList.remove("active");
  });
});
