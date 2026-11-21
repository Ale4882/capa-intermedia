
const registroForm = document.getElementById("registroForm");

if (registroForm) {
    const mensaje = document.getElementById("registroMessage");
    const boton = document.getElementById("registroButton");

    registroForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        const nombre_completo = document.getElementById("name").value.trim();
        const nombre_usuario = document.getElementById("username").value.trim();
        const genero = document.getElementById("gender").value;
        const fecha_nacimiento = document.getElementById("birthdate").value;
        const correo = document.getElementById("registerEmail").value.trim();
        const contrasena = document.getElementById("registerPassword").value;
        const confirmar = document.getElementById("confirmPassword").value;

        const rolSeleccionado = document.querySelector(
            'input[name="role"]:checked'
        );

        mensaje.textContent = "";
        mensaje.style.color = "";

        // Validaciones del formulario
        if (!nombre_completo || !nombre_usuario || !correo || !contrasena || !rolSeleccionado) {
            mensaje.textContent = "Completa todos los campos obligatorios.";
            mensaje.style.color = "#dc3545";
            return;
        }

        if (nombre_usuario.length < 3 || nombre_usuario.length > 40) {
            mensaje.textContent = "El nombre de usuario debe tener entre 3 y 40 caracteres.";
            mensaje.style.color = "#dc3545";
            return;
        }

        if (contrasena !== confirmar) {
            mensaje.textContent = "Las contraseñas no coinciden.";
            mensaje.style.color = "#dc3545";
            return;
        }

        const contrasenaValida =
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/;

        if (!contrasenaValida.test(contrasena)) {
            mensaje.textContent =
                "La contraseña debe tener mínimo 8 caracteres, mayúscula, minúscula, número y carácter especial.";
            mensaje.style.color = "#dc3545";
            return;
        }

        const datosRegistro = {
            nombre_completo,
            nombre_usuario,
            correo,
            contrasena,
            genero: genero || "prefiero_no_especificarlo",
            fecha_nacimiento: fecha_nacimiento || null,
            rol: rolSeleccionado.value
        };

        boton.disabled = true;
        boton.textContent = "Creando cuenta...";

        try {
            const respuesta = await fetch(
                "http://localhost:8081/NUMERA/backend/api/auth/registro",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    credentials: "include",
                    body: JSON.stringify(datosRegistro)
                }
            );

            const datos = await respuesta.json();

            if (!respuesta.ok || !datos.exito) {
                mensaje.textContent =
                    datos.mensaje || "No fue posible crear la cuenta.";
                mensaje.style.color = "#dc3545";
                return;
            }

            mensaje.textContent =
                "¡Cuenta creada correctamente! Ahora puedes iniciar sesión.";
            mensaje.style.color = "#198754";

            registroForm.reset();

            setTimeout(() => {
                window.location.href = "login.html";
            }, 1500);

        } catch (error) {
            console.error("Error en el registro:", error);

            mensaje.textContent =
                "No se pudo conectar con el servidor. Intenta nuevamente.";
            mensaje.style.color = "#dc3545";

        } finally {
            boton.disabled = false;
            boton.textContent = "Crear cuenta";
        }
    });
}
