
<?php

class Curso
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
            // Consumir resultados adicionales.
        }

        $stmt->closeCursor();

        return $resultado;
    }

    private function asociarCategorias(
        int $idCurso,
        array $categorias
    ): void {
        foreach ($categorias as $idCategoria) {
            $this->ejecutar(
                "CALL SP_AgregarCategoriaCurso(?, ?)",
                [$idCurso, $idCategoria]
            );
        }
    }

    public function crear(
        int $instructor,
        string $titulo,
        string $descripcion,
        ?string $imagen,
        float $precio,
        int $precioPorNivel,
        array $categorias
    ): int {
        try {
            $this->db->beginTransaction();

            $resultado = $this->ejecutar(
                "CALL SP_CrearCurso(?, ?, ?, ?, ?, ?)",
                [
                    $instructor,
                    $titulo,
                    $descripcion,
                    $imagen,
                    $precio,
                    $precioPorNivel
                ]
            );

            $idCurso = (int)($resultado[0]['id_curso'] ?? 0);

            if ($idCurso <= 0) {
                throw new RuntimeException(
                    'No se pudo obtener el ID del curso.'
                );
            }

            $this->asociarCategorias($idCurso, $categorias);

            $this->db->commit();

            return $idCurso;

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function cursosInstructor(int $instructor): array
    {
        return $this->ejecutar(
            "CALL SP_CursosInstructor(?)",
            [$instructor]
        );
    }

    public function catalogo(): array
    {
        return $this->ejecutar(
            "CALL SP_CatalogoCursos()"
        );
    }

    public function cursosPorCategoria(int $idCategoria): array
    {
        return $this->ejecutar(
            "CALL SP_CursosPorCategoria(?)",
            [$idCategoria]
        );
    }

    public function consultar(int $idCurso): array
    {
        $cursos = $this->ejecutar(
            "CALL SP_ConsultarCurso(?)",
            [$idCurso]
        );

        if (!$cursos) {
            return [];
        }

        $categorias = $this->ejecutar(
            "CALL SP_CategoriasDeCurso(?)",
            [$idCurso]
        );

        $cursos[0]['categorias'] = $categorias;

        return $cursos;
    }

    public function modificar(
        int $idCurso,
        int $instructor,
        string $titulo,
        string $descripcion,
        ?string $imagen,
        float $precio,
        int $precioPorNivel,
        array $categorias
    ): array {
        try {
            $this->db->beginTransaction();

            $resultado = $this->ejecutar(
                "CALL SP_ModificarCurso(?, ?, ?, ?, ?, ?, ?)",
                [
                    $idCurso,
                    $instructor,
                    $titulo,
                    $descripcion,
                    $imagen,
                    $precio,
                    $precioPorNivel
                ]
            );

            $afectados = (int)($resultado[0]['afectados'] ?? 0);

            if ($afectados === 0) {
                throw new DomainException(
                    'Curso no encontrado o no pertenece al instructor.'
                );
            }

            $this->ejecutar(
                "CALL SP_LimpiarCategoriasCurso(?)",
                [$idCurso]
            );

            $this->asociarCategorias($idCurso, $categorias);

            $this->db->commit();

            return ['afectados' => $afectados];

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function cambiarEstado(
        int $idCurso,
        int $instructor,
        int $publicado
    ): array {
        return $this->ejecutar(
            "CALL SP_CambiarEstadoCurso(?, ?, ?)",
            [$idCurso, $instructor, $publicado]
        );
    }

    public function eliminar(
        int $idCurso,
        int $instructor
    ): array {
        return $this->ejecutar(
            "CALL SP_EliminarCurso(?, ?)",
            [$idCurso, $instructor]
        );
    }
}
