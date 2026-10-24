<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse | NUMERA</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

    <header class="auth-header">
        <a href="index.html" class="logo">√ <span>NUMERA</span></a>
        <button class="theme-button" id="themeButton" title="Cambiar tema">☾</button>
    </header>

    <main class="auth-container">
        <section class="auth-card register-card">
            <div class="auth-symbol">+</div>
            <h1>Crear cuenta</h1>
            <p class="auth-subtitle">Únete a NUMERA y comienza a aprender.</p>

            <!-- Formulario adaptado para MVC -->
            <form action="index.php?controller=auth&action=register" method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label for="name">Nombre completo</label>
                    <input type="text" id="name" name="name" placeholder="Tu nombre completo" required>
                </div>

                <div class="form-group">
                    <label for="gender">Género</label>
                    <select id="gender" name="gender" required>
                        <option value="">Selecciona una opción</option>
                        <option value="Femenino">Femenino</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Otro">Otro</option>
                        <option value="Prefiero no especificarlo">Prefiero no especificarlo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="birthdate">Fecha de nacimiento</label>
                    <input type="date" id="birthdate" name="birthdate" required>
                </div>

                <div class="form-group">
                    <label for="registerEmail">Correo electrónico</label>
                    <input type="email" id="registerEmail" name="email" placeholder="ejemplo@correo.com" required>
                </div>

                <div class="form-group">
                    <label for="registerPassword">Contraseña</label>
                    <input type="password" id="registerPassword" name="password" placeholder="Crea una contraseña" required>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirmar contraseña</label>
                    <input type="password" id="confirmPassword" name="confirm_password" placeholder="Repite tu contraseña" required>
                </div>

                <div class="form-group">
                    <label for="photo">Foto de perfil</label>
                    <input type="file" id="photo" name="photo" accept="image/*">
                </div>

                <div class="form-group">
                    <label>Tipo de cuenta</label>
                    <div class="role-options">
                        <label class="role-option">
                            <input type="radio" name="role" value="Estudiante" required>
                            <span>🎓 Estudiante</span>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="Instructor" required>
                            <span>∑ Instructor</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="auth-submit" style="border:none; cursor:pointer; width:100%; text-align:center;">
                    Crear cuenta
                </button>

            </form>

            <p class="auth-switch">
                ¿Ya tienes una cuenta? <a href="login.html">Inicia sesión</a>
            </p>

            <a href="index.html" class="back-home">← Regresar al inicio</a>
        </section>

        <div class="auth-decoration register-decoration">
            <div class="math-expression">∫ x² dx = x³ / 3 + C</div>
            <div class="math-symbol symbol-one">π</div>
            <div class="math-symbol symbol-two">Σ</div>
            <div class="math-symbol symbol-three">∞</div>
            <div class="cat-decoration">/\_/\<br>( ^.^ )<br> > ^ <</div>
        </div>
    </main>

    <script src="js/script.js"></script>
</body>
</html>