
<?php

class Nivel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    private function ejecutar(string $sql, array $datos = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($datos);

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        while ($stmt->nextRowset()) {
            // Consumir resultados adicionales de los procedimientos.
        }

        $stmt->closeCursor();

        return $resultado;
    }

    // ======================================
    // NIVELES
    // ======================================

    public function crear(
        int $idCurso,
        int $idInstructor,
        string $titulo,
        ?string $descripcion,
        int $orden,
        float $precio
    ): int {
        $resultado = $this->ejecutar(
            "CALL SP_CrearNivel(?, ?, ?, ?, ?, ?)",
            [
                $idCurso,
                $idInstructor,
                $titulo,
                $descripcion,
                $orden,
                $precio
            ]
        );

        $id = (int)($resultado[0]['id_nivel'] ?? 0);

        if ($id <= 0) {
            throw new RuntimeException('No se pudo obtener el ID del nivel.');
        }

        return $id;
    }

    public function consultarPorCurso(
        int $idCurso,
        int $idInstructor
    ): array {
        return $this->ejecutar(
            "CALL SP_NivelesCurso(?, ?)",
            [$idCurso, $idInstructor]
        );
    }

    public function modificar(
        int $idNivel,
        int $idInstructor,
        string $titulo,
        ?string $descripcion,
        int $orden,
        float $precio
    ): array {
        return $this->ejecutar(
            "CALL SP_ModificarNivel(?, ?, ?, ?, ?, ?)",
            [
                $idNivel,
                $idInstructor,
                $titulo,
                $descripcion,
                $orden,
                $precio
            ]
        );
    }

    public function cambiarEstado(
        int $idNivel,
        int $idInstructor,
        int $activo
    ): array {
        return $this->ejecutar(
            "CALL SP_CambiarEstadoNivel(?, ?, ?)",
            [
                $idNivel,
                $idInstructor,
                $activo
            ]
        );
    }

    // ======================================
    // CONTENIDOS
    // ======================================

    public function crearContenido(
        int $idNivel,
        int $idInstructor,
        string $tipo,
        string $titulo,
        ?string $descripcion,
        ?string $rutaArchivo,
        ?string $urlExterna,
        ?string $textoContenido,
        int $orden
    ): int {
        $resultado = $this->ejecutar(
            "CALL SP_CrearContenidoNivel(?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $idNivel,
                $idInstructor,
                $tipo,
                $titulo,
                $descripcion,
                $rutaArchivo,
                $urlExterna,
                $textoContenido,
                $orden
            ]
        );

        $id = (int)($resultado[0]['id_contenido'] ?? 0);

        if ($id <= 0) {
            throw new RuntimeException('No se pudo obtener el ID del contenido.');
        }

        return $id;
    }

    public function consultarContenidos(
        int $idNivel,
        int $idInstructor
    ): array {
        return $this->ejecutar(
            "CALL SP_ContenidosNivel(?, ?)",
            [$idNivel, $idInstructor]
        );
    }

    public function modificarContenido(
        int $idContenido,
        int $idInstructor,
        string $tipo,
        string $titulo,
        ?string $descripcion,
        ?string $rutaArchivo,
        ?string $urlExterna,
        ?string $textoContenido,
        int $orden
    ): array {
        return $this->ejecutar(
            "CALL SP_ModificarContenidoNivel(?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $idContenido,
                $idInstructor,
                $tipo,
                $titulo,
                $descripcion,
                $rutaArchivo,
                $urlExterna,
                $textoContenido,
                $orden
            ]
        );
    }

    public function eliminarContenido(
        int $idContenido,
        int $idInstructor
    ): array {
        return $this->ejecutar(
            "CALL SP_EliminarContenidoNivel(?, ?)",
            [
                $idContenido,
                $idInstructor
            ]
        );
    }
}
