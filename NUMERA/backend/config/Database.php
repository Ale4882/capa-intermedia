
<?php

class Database
{
    private static ?PDO $conexion = null;

    public static function conectar(): PDO
    {
        if (self::$conexion !== null) {
            return self::$conexion;
        }

        $rutaEnv = dirname(__DIR__, 2) . '/.env';

        if (!file_exists($rutaEnv)) {
            throw new RuntimeException('No se encontró el archivo de configuración.');
        }

        $config = parse_ini_file($rutaEnv, false, INI_SCANNER_RAW);

        if ($config === false) {
            throw new RuntimeException('No se pudo leer la configuración.');
        }

        $host = $config['DB_HOST'] ?? '127.0.0.1';
        $puerto = $config['DB_PORT'] ?? '3307';
        $nombreBD = $config['DB_NAME'] ?? 'numera';
        $usuario = $config['DB_USER'] ?? 'root';
        $contrasena = $config['DB_PASS'] ?? '';

        $dsn = "mysql:host=$host;port=$puerto;dbname=$nombreBD;charset=utf8mb4";

        self::$conexion = new PDO(
            $dsn,
            $usuario,
            $contrasena,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );

        return self::$conexion;
    }
}
