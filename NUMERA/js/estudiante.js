
document.addEventListener("DOMContentLoaded", async () => {

    const nombreEstudiante = document.getElementById("nombreEstudiante");
    const nombreMini = document.getElementById("nombreMini");
    const avatarEstudiante = document.getElementById("avatarEstudiante");

    try {
        const respuesta = await fetch(
            "http://localhost:8081/NUMERA/backend/api/auth/sesion",
            {
                method: "GET",
                credentials: "include"
            }
        );

        const datos = await respuesta.json();

        // Si no hay sesión activa, regresar al login.
        if (!respuesta.ok || !datos.exito || !datos.usuario) {
            window.location.href = "login.html";
            return;
        }

        const usuario = datos.usuario;

        // Comprobar que la cuenta sea de estudiante.
        if (usuario.rol !== "estudiante") {
            const destinos = {
                instructor: "profesor.html",
                administrador: "administrador.html"
            };

            window.location.href = destinos[usuario.rol] || "login.html";
            return;
        }

        // Mostrar el nombre real.
        const nombre = usuario.nombre_completo || usuario.nombre_usuario;

        if (nombreEstudiante) {
            nombreEstudiante.textContent = nombre;
        }

        if (nombreMini) {
            nombreMini.textContent = nombre;
        }

        // Mostrar la inicial del nombre.
        if (avatarEstudiante) {
            avatarEstudiante.textContent = nombre.charAt(0).toUpperCase();
        }

    } catch (error) {
        console.error("Error al consultar la sesión:", error);
        window.location.href = "login.html";
    }

});



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
