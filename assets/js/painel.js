document.addEventListener("DOMContentLoaded", function() {
  const toggles = document.querySelectorAll(".submenu-toggle");

  toggles.forEach(toggle => {
    toggle.addEventListener("click", function() {
      const submenu = this.parentElement;
      submenu.classList.toggle("open");
    });
  });
});

document.addEventListener("DOMContentLoaded", () => {
  const btnLogout = document.getElementById("btnLogout");
  const modal = document.getElementById("logoutModal");
  const cancel = document.getElementById("cancelLogout");

  btnLogout.addEventListener("click", (e) => {
    e.preventDefault();
    modal.classList.add("active");
  });

  cancel.addEventListener("click", () => {
    modal.classList.remove("active");
  });
});
