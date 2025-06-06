const toggle = document.getElementById("theme-toggle");
const logoImages = document.querySelectorAll(".logo-img"); 

function updateLogos(theme) {
    const logoSrc = theme === "dark" ? "https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/empresa//logo-white.png" : "https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/empresa//logo-black.png";
    logoImages.forEach(img => {
        img.src = logoSrc;
    });
}

const currentTheme = localStorage.getItem("theme");
if (currentTheme === "dark") {
    document.body.classList.add("dark-mode");
    toggle.checked = true;
    updateLogos("dark"); 
} else {
    document.body.classList.remove("dark-mode"); 
    toggle.checked = false;
    updateLogos("light"); 
}

toggle.addEventListener("change", () => {
    if (toggle.checked) {
        document.body.classList.add("dark-mode");
        localStorage.setItem("theme", "dark");
        updateLogos("dark"); 
    } else {
        document.body.classList.remove("dark-mode");
        localStorage.setItem("theme", "light");
        updateLogos("light"); 
    }
});

