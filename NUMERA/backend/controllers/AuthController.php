
<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Usuario.php';

class AuthController extends Controller
{
    private Usuario $usuario;

    public function __construct()
    {
        $this->usuario = new Usuario();

        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'httponly' => true,
                'secure' => !empty($_SERVER['HTTPS'])
                    && $_SERVER['HTTPS'] !== 'off',
                'samesite' => 'Lax'
            ]);

            session_start();
        }
    }

    public function registrar(): void
    {
        $datos = Request::json();

        $nombre = trim($datos['nombre_completo'] ?? '');
        $usuario = trim($datos['nombre_usuario'] ?? '');
        $correo = trim($datos['correo'] ?? '');
        $contrasena = $datos['contrasena'] ?? '';
        $genero = $datos['genero'] ?? 'prefiero_no_especificarlo';
        $fecha = $datos['fecha_nacimiento'] ?? null;
        $rol = $datos['rol'] ?? 'estudiante';

        if (
            $nombre === '' ||
            $usuario === '' ||
            $correo === '' ||
            $contrasena === ''
        ) {
            $this->responder([
                'exito' => false,
                'mensaje' => 'Completa todos los campos obligatorios.'
            ], 400);
        }

        if (
            !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
            strlen($usuario) < 3 ||
            strlen($usuario) > 40 ||
            strlen($nombre) > 120
        ) {
            $this->responder([
                'exito' => false,
                'mensaje' => 'Verifica el correo y los datos ingresados.'
            ], 400);
        }

        if (!preg_match('/^[\p{L}\p{N}_.-]+$/u', $usuario)) {
            $this->responder([
                'exito' => false,
                'mensaje' => 'El nombre de usuario contiene caracteres no permitidos.'
            ], 400);
        }

        if (
            strlen($contrasena) < 8 ||
            !preg_match('/[A-Z]/', $contrasena) ||
            !preg_match('/[a-z]/', $contrasena) ||
            !preg_match('/[0-9]/', $contrasena) ||
            !preg_match('/[^A-Za-z0-9]/', $contrasena)
        ) {
            $this->responder([
                'exito' => false,
                'mensaje' => 'La contraseña debe tener al menos 8 caracteres, mayúscula, minúscula, número y carácter especial.'
            ], 400);
        }

        if (!in_array($rol, ['estudiante', 'instructor'], true)) {
            $this->responder([
                'exito' => false,
                'mensaje' => 'El rol seleccionado no es válido.'
            ], 400);
        }

        $generos = [
            'femenino',
            'masculino',
            'otro',
            'prefiero_no_especificarlo'
        ];

        if (!in_array($genero, $generos, true)) {
            $this->responder([
                'exito' => false,
                'mensaje' => 'El género seleccionado no es válido.'
            ], 400);
        }

        if ($fecha !== null) {
            $fechaObjeto = DateTime::createFromFormat('!Y-m-d', $fecha);

            if (!$fechaObjeto || $fechaObjeto->format('Y-m-d') !== $fecha) {
                $this->responder([
                    'exito' => false,
                    'mensaje' => 'La fecha de nacimiento no es válida.'
                ], 400);
            }
        }

        $hash = password_hash($contrasena, PASSWORD_DEFAULT);

        $resultado = $this->usuario->registrar(
            $nombre,
            $usuario,
            $correo,
            $hash,
            $genero,
            $fecha,
            $rol
        );

        if (empty($resultado['exito'])) {
            $this->responder([
                'exito' => false,
                'mensaje' => $resultado['mensaje'] ?? 'No se pudo registrar el usuario.',
                'campo' => $resultado['campo'] ?? null
            ], 409);
        }

        $this->responder([
            'exito' => true,
            'mensaje' => $resultado['mensaje'],
            'id_usuario' => (int) $resultado['id_usuario']
        ], 201);
    }

    public function iniciarSesion(): void
    {
        $datos = Request::json();

        $identificador = trim($datos['identificador'] ?? '');
        $contrasena = $datos['contrasena'] ?? '';

        if ($identificador === '' || $contrasena === '') {
            $this->responder([
                'exito' => false,
                'mensaje' => 'Ingresa tu usuario o correo y contraseña.'
            ], 400);
        }

        $usuario = $this->usuario->buscarParaLogin($identificador);

        if (!$usuario) {
            $this->responder([
                'exito' => false,
                'mensaje' => 'Credenciales incorrectas.'
            ], 401);
        }

        if (empty($usuario['activo']) || !empty($usuario['bloqueado'])) {
            $this->responder([
                'exito' => false,
                'mensaje' => 'La cuenta está desactivada o bloqueada. Contacta al administrador.'
            ], 403);
        }

        if (!password_verify($contrasena, $usuario['contrasena'])) {
            $intentos = $this->usuario->registrarIntentoFallido(
                (int) $usuario['id_usuario']
            );

            $this->responder([
                'exito' => false,
                'mensaje' => !empty($intentos['bloqueado'])
                    ? 'Tu cuenta fue bloqueada por superar los intentos permitidos.'
                    : 'Credenciales incorrectas.',
                'intentos_restantes' => max(
                    0,
                    3 - (int) ($intentos['intentos_fallidos'] ?? 0)
                )
            ], 401);
        }

        $this->usuario->reiniciarIntentos(
            (int) $usuario['id_usuario']
        );

        session_regenerate_id(true);

        $_SESSION['usuario'] = [
            'id_usuario' => (int) $usuario['id_usuario'],
            'nombre_usuario' => $usuario['nombre_usuario'],
            'rol' => $usuario['rol']
        ];

        $this->responder([
            'exito' => true,
            'mensaje' => 'Inicio de sesión correcto.',
            'usuario' => $_SESSION['usuario']
        ]);
    }

    public function consultarSesion(): void
    {
        if (empty($_SESSION['usuario']['id_usuario'])) {
            $this->responder([
                'exito' => false,
                'mensaje' => 'No hay una sesión activa.'
            ], 401);
        }

        $usuario = $this->usuario->consultarSesion(
            (int) $_SESSION['usuario']['id_usuario']
        );

        if (!$usuario || empty($usuario['activo']) || !empty($usuario['bloqueado'])) {
            $_SESSION = [];
            session_destroy();

            $this->responder([
                'exito' => false,
                'mensaje' => 'La sesión ya no es válida.'
            ], 401);
        }

        unset($usuario['contrasena']);

        $this->responder([
            'exito' => true,
            'usuario' => $usuario
        ]);
    }

    public function cerrarSesion(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $parametros = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $parametros['path'],
                $parametros['domain'],
                $parametros['secure'],
                $parametros['httponly']
            );
        }

        session_destroy();

        $this->responder([
            'exito' => true,
            'mensaje' => 'Sesión cerrada correctamente.'
        ]);
    }
}
