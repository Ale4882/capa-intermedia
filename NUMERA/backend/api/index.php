
<?php

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Router.php';

$origen = $_SERVER['HTTP_ORIGIN'] ?? '';
$origenPermitido = 'http://localhost:8081';

if ($origen === $origenPermitido) {
    header('Access-Control-Allow-Origin: ' . $origenPermitido);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Vary: Origin');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,
            'secure' => !empty($_SERVER['HTTPS'])
                && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax'
        ]);

        session_start();
    }

    $router = new Router();

    require_once __DIR__ . '/../routes/routes.php';

    $router->ejecutar();

} catch (Throwable $error) {
    error_log($error->getMessage());

    Response::json([
        'exito' => false,
        'mensaje' => 'Ocurrió un error interno en el servidor.'
    ], 500);
}
