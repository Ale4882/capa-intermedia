
document.addEventListener("DOMContentLoaded", async () => {

    const rolEsperado = document.body.dataset.rol;
    const destinos = {
        estudiante: "estudiante.html",
        instructor: "profesor.html",
        administrador: "admin.html"
    };

    try {
        const respuesta = await fetch(
            "http://localhost:8081/NUMERA/backend/api/auth/sesion",
            {
                method: "GET",
                credentials: "include"
            }
        );

        const datos = await respuesta.json();

        if (!respuesta.ok || !datos.exito || !datos.usuario) {
            window.location.href = "login.html";
            return;
        }

        const usuario = datos.usuario;

        if (usuario.rol !== rolEsperado) {
            window.location.href = destinos[usuario.rol] || "login.html";
            return;
        }

        const nombre = usuario.nombre_completo || usuario.nombre_usuario;

        const nombreUsuario = document.getElementById("nombreUsuario");
        const nombreMini = document.getElementById("nombreMini");
        const avatar = document.getElementById("avatarUsuario");

        if (nombreUsuario) nombreUsuario.textContent = nombre;
        if (nombreMini) nombreMini.textContent = nombre;

        if (avatar) {
            avatar.textContent = nombre.charAt(0).toUpperCase();
        }

    } catch (error) {
        console.error("Error al consultar sesión:", error);
        window.location.href = "login.html";
    }

});

// Cerrar sesión
const cerrarSesion = document.getElementById("cerrarSesion");

if (cerrarSesion) {
    cerrarSesion.addEventListener("click", async (event) => {
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
            console.error(error);
            alert("No se pudo conectar con el servidor.");
        }
    });
}
