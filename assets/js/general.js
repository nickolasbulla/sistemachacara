document.addEventListener("DOMContentLoaded", () => {

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