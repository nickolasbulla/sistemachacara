document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', () => {
            const input = icon.closest('.password-wrapper').querySelector('input');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });

    document.querySelectorAll("label").forEach(label => {
        label.innerHTML = label.innerHTML.replace(/\*/g, '<span class="asterisco">*</span>');
    });

    const textareas = document.querySelectorAll("textarea");

    textareas.forEach(textarea => {

        const autoResize = () => {
            textarea.style.height = "auto";
            textarea.style.height = textarea.scrollHeight + "px";
        };

        textarea.addEventListener("input", autoResize);

        autoResize();
    });

});