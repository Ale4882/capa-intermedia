
<?php

require_once __DIR__ . '/../core/Response.php';

class AuthMiddleware
{
    public static function autenticar(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario']['id_usuario'])) {
            Response::json([
                'exito' => false,
                'mensaje' => 'Debes iniciar sesión.'
            ], 401);
        }
    }

    public static function permitirRoles(array $roles): void
    {
        self::autenticar();

        $rol = $_SESSION['usuario']['rol'] ?? '';

        if (!in_array($rol, $roles, true)) {
            Response::json([
                'exito' => false,
                'mensaje' => 'No tienes permisos para acceder a este recurso.'
            ], 403);
        }
    }
}
