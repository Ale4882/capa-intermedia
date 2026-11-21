
<?php

require_once __DIR__ . '/../config/Database.php';

class Usuario
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = Database::conectar();
    }

    public function registrar(
        string $nombreCompleto,
        string $nombreUsuario,
        string $correo,
        string $contrasenaHash,
        string $genero,
        ?string $fechaNacimiento,
        string $rol
    ): array {
        $stmt = $this->conexion->prepare(
            "CALL SP_RegistrarUsuario(?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $nombreCompleto,
            $nombreUsuario,
            $correo,
            $contrasenaHash,
            $genero,
            $fechaNacimiento ?: null,
            $rol
        ]);

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->closeCursor();

        return $resultado ?: [];
    }

    public function buscarParaLogin(string $identificador): ?array
    {
        $stmt = $this->conexion->prepare(
            "CALL SP_ObtenerUsuarioLogin(?)"
        );

        $stmt->execute([$identificador]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->closeCursor();

        return $usuario ?: null;
    }

    public function registrarIntentoFallido(int $idUsuario): array
    {
        $stmt = $this->conexion->prepare(
            "CALL SP_ActualizarIntentosFallidos(?)"
        );

        $stmt->execute([$idUsuario]);

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->closeCursor();

        return $resultado ?: [];
    }

    public function reiniciarIntentos(int $idUsuario): void
    {
        $stmt = $this->conexion->prepare(
            "CALL SP_ReiniciarIntentosLogin(?)"
        );

        $stmt->execute([$idUsuario]);

        $stmt->closeCursor();
    }

    public function consultarSesion(int $idUsuario): ?array
    {
        $stmt = $this->conexion->prepare(
            "CALL SP_ConsultarUsuarioSesion(?)"
        );

        $stmt->execute([$idUsuario]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->closeCursor();

        return $usuario ?: null;
    }
}
