
<?php

class Inscripcion
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    private function ejecutar(string $procedimiento, array $parametros = []): array
    {
        $marcadores = implode(',', array_fill(0, count($parametros), '?'));

        $stmt = $this->db->prepare(
            "CALL {$procedimiento}({$marcadores})"
        );

        $stmt->execute($parametros);

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        while ($stmt->nextRowset()) {
            // Limpiar resultados adicionales del procedimiento.
        }

        $stmt->closeCursor();

        return $resultado;
    }

    public function inscribirCursoGratis(int $idUsuario, int $idCurso): array
    {
        return $this->ejecutar(
            'SP_InscribirCursoGratis',
            [$idUsuario, $idCurso]
        );
    }

    public function inscribirNivelGratis(int $idUsuario, int $idNivel): array
    {
        return $this->ejecutar(
            'SP_InscribirNivelGratis',
            [$idUsuario, $idNivel]
        );
    }

    public function misCursos(int $idUsuario): array
    {
        return $this->ejecutar(
            'SP_MisCursosInscritos',
            [$idUsuario]
        );
    }

    public function validarAcceso(int $idUsuario, int $idNivel): array
    {
        return $this->ejecutar(
            'SP_ValidarAccesoNivel',
            [$idUsuario, $idNivel]
        );
    }

    public function contenidosEstudiante(int $idUsuario, int $idNivel): array
    {
        return $this->ejecutar(
            'SP_ContenidosNivelEstudiante',
            [$idUsuario, $idNivel]
        );
    }

    public function actualizarProgreso(
        int $idUsuario,
        int $idNivel,
        float $porcentaje
    ): array {
        return $this->ejecutar(
            'SP_ActualizarProgresoNivel',
            [$idUsuario, $idNivel, $porcentaje]
        );
    }

    public function progresoCurso(int $idUsuario, int $idCurso): array
    {
        return $this->ejecutar(
            'SP_ConsultarProgresoCurso',
            [$idUsuario, $idCurso]
        );
    }
}
