/* =====================================
   MODO CLARO / OSCURO
===================================== */

const themeButton = document.getElementById("themeButton");

if (themeButton) {

    const savedTheme = localStorage.getItem("numeraDarkMode");

    if (savedTheme === "true") {

        document.body.classList.add("dark-mode");
        themeButton.textContent = "☀";

    } else {

        themeButton.textContent = "☾";

    }


    themeButton.addEventListener("click", () => {

        document.body.classList.toggle("dark-mode");

        const darkMode =
            document.body.classList.contains("dark-mode");

        localStorage.setItem(
            "numeraDarkMode",
            darkMode
        );

        themeButton.textContent =
            darkMode ? "☀" : "☾";

    });

}


/* =====================================
   BUSCADOR
===================================== */

const searchInput =
    document.getElementById("searchInput");

const searchButton =
    document.getElementById("searchButton");


if (searchInput && searchButton) {

    searchButton.addEventListener("click", () => {

        const search =
            searchInput.value.trim();

        if (search === "") {

            alert("Escribe algo para buscar un curso.");

            return;

        }

        alert(
            "Buscando cursos relacionados con: " +
            search
        );

    });

}


/* =====================================
   BOTONES DE CURSOS
===================================== */

const courseButtons =
    document.querySelectorAll(".course-button");


courseButtons.forEach(button => {

    button.addEventListener("click", () => {

        alert("Aquí se abrirá el detalle del curso.");

    });

});