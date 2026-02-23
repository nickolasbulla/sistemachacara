document.addEventListener("DOMContentLoaded", () => {
    
    const toggle = document.getElementById("toggleSenha");
    const senhaInput = document.getElementById("senha");

    if (!toggle || !senhaInput) return;

    toggle.addEventListener("click", () => {

        const isPassword = senhaInput.type === "password";

        senhaInput.type = isPassword ? "text" : "password";

        toggle.classList.toggle("fa-eye");
        toggle.classList.toggle("fa-eye-slash");

    });
});