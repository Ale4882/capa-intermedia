
<?php

require_once __DIR__ . '/../config/Database.php';

class SistemaModel
{
    public function comprobarConexion(): bool
    {
        $conexion = Database::conectar();

        return $conexion->getAttribute(
            PDO::ATTR_CONNECTION_STATUS
        ) !== false;
    }
}
