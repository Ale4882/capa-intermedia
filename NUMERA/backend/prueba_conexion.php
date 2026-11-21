
<?php

require_once __DIR__ . '/config/Database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $conexion = Database::conectar();

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Conexión a la base de datos NUMERA exitosa.'
    ]);

} catch (Throwable $error) {

    error_log($error->getMessage());

    http_response_code(500);

    echo json_encode([
        'exito' => false,
        'mensaje' => 'No fue posible conectar con la base de datos.'
    ]);
}
