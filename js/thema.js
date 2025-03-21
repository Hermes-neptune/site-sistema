const toggle = document.getElementById("theme-toggle");

// Verifica o estado salvo no localStorage ao carregar a página
if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark-mode");
    toggle.checked = true;
}

// Adiciona o evento de mudança
toggle.addEventListener("change", () => {
    if (toggle.checked) {
        document.body.classList.add("dark-mode");
        localStorage.setItem("theme", "dark");
    } else {
        document.body.classList.remove("dark-mode");
        localStorage.setItem("theme", "light");
    }
});