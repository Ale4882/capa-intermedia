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


/* =====================================
   INICIO DE SESIÓN - API NUMERA
===================================== */

const loginForm = document.getElementById("loginForm");

if (loginForm) {

    const loginMessage = document.getElementById("loginMessage");
    const loginButton = document.getElementById("loginButton");

    loginForm.addEventListener("submit", async (event) => {

        event.preventDefault();

        const identificador = document.getElementById("email").value.trim();
        const contrasena = document.getElementById("password").value;

        loginMessage.textContent = "";
        loginMessage.style.color = "";

        loginButton.disabled = true;
        loginButton.textContent = "Iniciando sesión...";

        try {

            const respuesta = await fetch(
                "http://localhost:8081/NUMERA/backend/api/auth/login",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    credentials: "include",
                    body: JSON.stringify({
                        identificador: identificador,
                        contrasena: contrasena
                    })
                }
            );

            const datos = await respuesta.json();

            if (!respuesta.ok || !datos.exito) {

                loginMessage.textContent =
                    datos.mensaje || "No fue posible iniciar sesión.";

                loginMessage.style.color = "#dc3545";

                return;
            }

            loginMessage.textContent = datos.mensaje;
            loginMessage.style.color = "#198754";

            // Redirección según el rol
            const destinos = {
                estudiante: "estudiante.html",
    instructor: "profesor.html",
    administrador: "admin.html"
            };

            const destino = destinos[datos.usuario.rol];

            if (!destino) {
                loginMessage.textContent =
                    "No se encontró un panel para este tipo de usuario.";
                loginMessage.style.color = "#dc3545";
                return;
            }

            setTimeout(() => {
                window.location.href = destino;
            }, 700);

        } catch (error) {

            console.error("Error al iniciar sesión:", error);

            loginMessage.textContent =
                "No se pudo conectar con el servidor. Intenta nuevamente.";

            loginMessage.style.color = "#dc3545";

        } finally {

            loginButton.disabled = false;
            loginButton.textContent = "Iniciar sesión";

        }

    });

}


const botonCerrarSesion = document.getElementById("cerrarSesion");

if (botonCerrarSesion) {
    botonCerrarSesion.addEventListener("click", async (event) => {
        event.preventDefault();

        try {
            const respuesta = await fetch(
                "http://localhost:8081/NUMERA/backend/api/auth/logout",
                {
                    method: "POST",
                    credentials: "include"
                }
            );

            const datos = await respuesta.json();

            if (respuesta.ok && datos.exito) {
                window.location.href = "login.html";
            } else {
                alert(datos.mensaje || "No se pudo cerrar la sesión.");
            }

        } catch (error) {
            console.error("Error al cerrar sesión:", error);
            alert("No se pudo conectar con el servidor.");
        }
    });
}
