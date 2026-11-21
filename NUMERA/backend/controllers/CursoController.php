
<?php

class CursoController
{
    private Curso $curso;

    public function __construct(Curso $curso)
    {
        $this->curso = $curso;
    }

    private function respuesta(int $codigo, array $datos): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function leerDatos(): array
    {
        $datos = json_decode(file_get_contents('php://input'), true);

        if (!is_array($datos)) {
            $this->respuesta(400, [
                'exito' => false,
                'mensaje' => 'El cuerpo de la solicitud debe ser JSON válido.'
            ]);
        }

        return $datos;
    }

    
private function usuarioActual(): ?array
{
    if (empty($_SESSION['usuario']['id_usuario'])) {
        return null;
    }

    return [
        'id' => (int) $_SESSION['usuario']['id_usuario'],
        'rol' => $_SESSION['usuario']['rol'] ?? ''
    ];
}


    private function exigirProfesor(): array
    {
        $usuario = $this->usuarioActual();

        if (!$usuario) {
            $this->respuesta(401, [
                'exito' => false,
                'mensaje' => 'Debes iniciar sesión.'
            ]);
        }

        if ($usuario['rol'] !== 'instructor') {
            $this->respuesta(403, [
                'exito' => false,
                'mensaje' => 'No tienes permisos de profesor.'
            ]);
        }

        return $usuario;
    }

    private function validarCurso(array $datos): array
    {
        $titulo = trim($datos['titulo'] ?? '');
        $descripcion = trim($datos['descripcion'] ?? '');
        $imagen = $datos['imagen'] ?? null;
        $precio = $datos['precio'] ?? null;
        $precioPorNivel = $datos['precio_por_nivel'] ?? null;
        $categorias = $datos['categorias'] ?? null;

        if ($titulo === '' || mb_strlen($titulo) > 255) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'El título es obligatorio y no debe superar 255 caracteres.'
            ]);
        }

        if (!is_numeric($precio) || (float)$precio < 0) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'El precio debe ser un número mayor o igual a cero.'
            ]);
        }

        if (!in_array((string)$precioPorNivel, ['0', '1'], true)) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'precio_por_nivel debe ser 0 o 1.'
            ]);
        }

        if ($imagen !== null && !is_string($imagen)) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'La imagen debe ser una ruta o null.'
            ]);
        }

        if (!is_array($categorias) || count($categorias) === 0) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'Debes seleccionar al menos una categoría.'
            ]);
        }

        $categoriasValidadas = [];

        foreach ($categorias as $idCategoria) {
            if (
                !filter_var($idCategoria, FILTER_VALIDATE_INT) ||
                (int)$idCategoria <= 0
            ) {
                $this->respuesta(422, [
                    'exito' => false,
                    'mensaje' => 'La lista de categorías contiene un ID inválido.'
                ]);
            }

            $categoriasValidadas[] = (int)$idCategoria;
        }

        $categoriasValidadas = array_values(
            array_unique($categoriasValidadas)
        );

        return [
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'imagen' => $imagen,
            'precio' => (float)$precio,
            'precio_por_nivel' => (int)$precioPorNivel,
            'categorias' => $categoriasValidadas
        ];
    }

    public function misCursos(): void
    {
        $usuario = $this->exigirProfesor();

        $this->respuesta(200, [
            'exito' => true,
            'cursos' => $this->curso->cursosInstructor($usuario['id'])
        ]);
    }

    public function catalogo(): void
    {
        $idCategoria = $_GET['categoria'] ?? null;

        if ($idCategoria !== null) {
            if (
                !filter_var($idCategoria, FILTER_VALIDATE_INT) ||
                (int)$idCategoria <= 0
            ) {
                $this->respuesta(400, [
                    'exito' => false,
                    'mensaje' => 'El ID de categoría no es válido.'
                ]);
            }

            $cursos = $this->curso->cursosPorCategoria(
                (int)$idCategoria
            );
        } else {
            $cursos = $this->curso->catalogo();
        }

        $this->respuesta(200, [
            'exito' => true,
            'cursos' => $cursos
        ]);
    }

    public function consultar(int $id): void
    {
        if ($id <= 0) {
            $this->respuesta(400, [
                'exito' => false,
                'mensaje' => 'ID inválido.'
            ]);
        }

        $resultado = $this->curso->consultar($id);

        if (!$resultado) {
            $this->respuesta(404, [
                'exito' => false,
                'mensaje' => 'Curso no encontrado.'
            ]);
        }

        $this->respuesta(200, [
            'exito' => true,
            'curso' => $resultado[0]
        ]);
    }

    public function crear(): void
    {
        $usuario = $this->exigirProfesor();
        $datos = $this->validarCurso($this->leerDatos());

        try {
            $idCurso = $this->curso->crear(
                $usuario['id'],
                $datos['titulo'],
                $datos['descripcion'],
                $datos['imagen'],
                $datos['precio'],
                $datos['precio_por_nivel'],
                $datos['categorias']
            );

            $this->respuesta(201, [
                'exito' => true,
                'mensaje' => 'Curso creado y categorías asociadas correctamente.',
                'id_curso' => $idCurso
            ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());

            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'No se pudo crear el curso. Verifica las categorías seleccionadas.'
            ]);
        }
    }

    public function modificar(int $id): void
    {
        $usuario = $this->exigirProfesor();

        if ($id <= 0) {
            $this->respuesta(400, [
                'exito' => false,
                'mensaje' => 'ID inválido.'
            ]);
        }

        $datos = $this->validarCurso($this->leerDatos());

        try {
            $this->curso->modificar(
                $id,
                $usuario['id'],
                $datos['titulo'],
                $datos['descripcion'],
                $datos['imagen'],
                $datos['precio'],
                $datos['precio_por_nivel'],
                $datos['categorias']
            );

            $this->respuesta(200, [
                'exito' => true,
                'mensaje' => 'Curso y categorías actualizados correctamente.'
            ]);

        } catch (DomainException $e) {
            $this->respuesta(404, [
                'exito' => false,
                'mensaje' => $e->getMessage()
            ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());

            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'No se pudo modificar el curso. Verifica los datos y las categorías.'
            ]);
        }
    }

    public function estado(int $id): void
    {
        $usuario = $this->exigirProfesor();
        $datos = $this->leerDatos();

        $publicado = $datos['publicado'] ?? null;

        if (
            $id <= 0 ||
            !in_array($publicado, [0, 1, '0', '1'], true)
        ) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'El ID o el estado del curso no son válidos.'
            ]);
        }

        $resultado = $this->curso->cambiarEstado(
            $id,
            $usuario['id'],
            (int)$publicado
        );

        if ((int)($resultado[0]['afectados'] ?? 0) === 0) {
            $this->respuesta(404, [
                'exito' => false,
                'mensaje' => 'Curso no encontrado o no te pertenece.'
            ]);
        }

        $this->respuesta(200, [
            'exito' => true,
            'mensaje' => 'Estado del curso actualizado.'
        ]);
    }

    public function eliminar(int $id): void
    {
        $usuario = $this->exigirProfesor();

        if ($id <= 0) {
            $this->respuesta(400, [
                'exito' => false,
                'mensaje' => 'ID inválido.'
            ]);
        }

        $resultado = $this->curso->eliminar(
            $id,
            $usuario['id']
        );

        if ((int)($resultado[0]['afectados'] ?? 0) === 0) {
            $this->respuesta(404, [
                'exito' => false,
                'mensaje' => 'Curso no encontrado o no te pertenece.'
            ]);
        }

        $this->respuesta(200, [
            'exito' => true,
            'mensaje' => 'Curso eliminado correctamente.'
        ]);
    }
}
