
<?php

class NivelController
{
    private Nivel $nivel;

    public function __construct(Nivel $nivel)
    {
        $this->nivel = $nivel;
    }

    private function respuesta(int $codigo, array $datos): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function usuarioActual(): ?array
    {
        if (empty($_SESSION['usuario']['id_usuario'])) {
            return null;
        }

        return [
            'id' => (int)$_SESSION['usuario']['id_usuario'],
            'rol' => $_SESSION['usuario']['rol'] ?? ''
        ];
    }

    private function exigirInstructor(): array
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
                'mensaje' => 'No tienes permisos de instructor.'
            ]);
        }

        return $usuario;
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

    private function validarNivel(array $datos): array
    {
        $titulo = trim($datos['titulo'] ?? '');
        $descripcion = $datos['descripcion'] ?? null;
        $orden = $datos['orden'] ?? null;
        $precio = $datos['precio'] ?? null;

        if (
            $titulo === '' ||
            mb_strlen($titulo) > 150
        ) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'El título es obligatorio y no debe superar 150 caracteres.'
            ]);
        }

        if (
            $descripcion !== null &&
            !is_string($descripcion)
        ) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'La descripción no es válida.'
            ]);
        }

        if (
            !filter_var($orden, FILTER_VALIDATE_INT) ||
            (int)$orden <= 0
        ) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'El orden debe ser un entero positivo.'
            ]);
        }

        if (!is_numeric($precio) || (float)$precio < 0) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'El precio debe ser mayor o igual a cero.'
            ]);
        }

        return [
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'orden' => (int)$orden,
            'precio' => (float)$precio
        ];
    }

    private function validarContenido(array $datos): array
    {
        $tipos = ['video', 'texto', 'pdf', 'imagen', 'enlace'];

        $tipo = $datos['tipo'] ?? '';
        $titulo = trim($datos['titulo'] ?? '');
        $descripcion = $datos['descripcion'] ?? null;
        $ruta = $datos['ruta_archivo'] ?? null;
        $url = $datos['url_externa'] ?? null;
        $texto = $datos['texto_contenido'] ?? null;
        $orden = $datos['orden'] ?? 1;

        if (!in_array($tipo, $tipos, true)) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'El tipo de contenido no es válido.'
            ]);
        }

        if ($titulo === '' || mb_strlen($titulo) > 150) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'El título es obligatorio y no debe superar 150 caracteres.'
            ]);
        }

        if (
            $descripcion !== null &&
            !is_string($descripcion)
        ) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'La descripción no es válida.'
            ]);
        }

        if (
            $ruta !== null &&
            !is_string($ruta)
        ) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'La ruta del archivo no es válida.'
            ]);
        }

        if (
            $url !== null &&
            (
                !is_string($url) ||
                mb_strlen($url) > 500 ||
                !filter_var($url, FILTER_VALIDATE_URL)
            )
        ) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'La URL externa no es válida.'
            ]);
        }

        if (
            $texto !== null &&
            !is_string($texto)
        ) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'El texto del contenido no es válido.'
            ]);
        }

        if (!filter_var($orden, FILTER_VALIDATE_INT) || (int)$orden <= 0) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'El orden debe ser un entero positivo.'
            ]);
        }

        if ($tipo === 'texto' && trim($texto ?? '') === '') {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'Debes proporcionar el texto del contenido.'
            ]);
        }

        if (
            in_array($tipo, ['video', 'enlace'], true) &&
            empty($url) &&
            empty($ruta)
        ) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'Debes proporcionar una URL o ruta para este contenido.'
            ]);
        }

        if (
            in_array($tipo, ['pdf', 'imagen'], true) &&
            empty($ruta) &&
            empty($url)
        ) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'Debes proporcionar la ruta o URL del archivo.'
            ]);
        }

        return [
            'tipo' => $tipo,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'ruta_archivo' => $ruta,
            'url_externa' => $url,
            'texto_contenido' => $texto,
            'orden' => (int)$orden
        ];
    }

    // ======================================
    // NIVELES
    // ======================================

    public function crear(int $idCurso): void
    {
        $usuario = $this->exigirInstructor();

        if ($idCurso <= 0) {
            $this->respuesta(400, [
                'exito' => false,
                'mensaje' => 'El ID del curso no es válido.'
            ]);
        }

        $datos = $this->validarNivel($this->leerDatos());

        try {
            $idNivel = $this->nivel->crear(
                $idCurso,
                $usuario['id'],
                $datos['titulo'],
                $datos['descripcion'],
                $datos['orden'],
                $datos['precio']
            );

            $this->respuesta(201, [
                'exito' => true,
                'mensaje' => 'Nivel creado correctamente.',
                'id_nivel' => $idNivel
            ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());

            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'No se pudo crear el nivel. Verifica el curso y que el orden no esté repetido.'
            ]);
        }
    }

    public function consultarPorCurso(int $idCurso): void
    {
        $usuario = $this->exigirInstructor();

        if ($idCurso <= 0) {
            $this->respuesta(400, [
                'exito' => false,
                'mensaje' => 'El ID del curso no es válido.'
            ]);
        }

        try {
            $niveles = $this->nivel->consultarPorCurso(
                $idCurso,
                $usuario['id']
            );

            $this->respuesta(200, [
                'exito' => true,
                'niveles' => $niveles
            ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());

            $this->respuesta(404, [
                'exito' => false,
                'mensaje' => 'No se pudieron consultar los niveles. Verifica que el curso te pertenezca.'
            ]);
        }
    }

    public function modificar(int $idNivel): void
    {
        $usuario = $this->exigirInstructor();

        if ($idNivel <= 0) {
            $this->respuesta(400, [
                'exito' => false,
                'mensaje' => 'El ID del nivel no es válido.'
            ]);
        }

        $datos = $this->validarNivel($this->leerDatos());

        try {
            $this->nivel->modificar(
                $idNivel,
                $usuario['id'],
                $datos['titulo'],
                $datos['descripcion'],
                $datos['orden'],
                $datos['precio']
            );

            $this->respuesta(200, [
                'exito' => true,
                'mensaje' => 'Nivel actualizado correctamente.'
            ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());

            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'No se pudo modificar el nivel. Verifica los datos y que el orden no esté ocupado.'
            ]);
        }
    }

    public function estado(int $idNivel): void
    {
        $usuario = $this->exigirInstructor();
        $datos = $this->leerDatos();

        $activo = $datos['activo'] ?? null;

        if (
            $idNivel <= 0 ||
            !in_array($activo, [0, 1, '0', '1'], true)
        ) {
            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'El ID o estado del nivel no son válidos.'
            ]);
        }

        try {
            $this->nivel->cambiarEstado(
                $idNivel,
                $usuario['id'],
                (int)$activo
            );

            $this->respuesta(200, [
                'exito' => true,
                'mensaje' => 'Estado del nivel actualizado correctamente.'
            ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());

            $this->respuesta(404, [
                'exito' => false,
                'mensaje' => 'No se pudo actualizar el nivel.'
            ]);
        }
    }

    // ======================================
    // CONTENIDOS
    // ======================================

    public function crearContenido(int $idNivel): void
    {
        $usuario = $this->exigirInstructor();

        if ($idNivel <= 0) {
            $this->respuesta(400, [
                'exito' => false,
                'mensaje' => 'El ID del nivel no es válido.'
            ]);
        }

        $datos = $this->validarContenido($this->leerDatos());

        try {
            $idContenido = $this->nivel->crearContenido(
                $idNivel,
                $usuario['id'],
                $datos['tipo'],
                $datos['titulo'],
                $datos['descripcion'],
                $datos['ruta_archivo'],
                $datos['url_externa'],
                $datos['texto_contenido'],
                $datos['orden']
            );

            $this->respuesta(201, [
                'exito' => true,
                'mensaje' => 'Contenido agregado correctamente.',
                'id_contenido' => $idContenido
            ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());

            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'No se pudo agregar el contenido. Verifica el nivel y los datos.'
            ]);
        }
    }

    public function consultarContenidos(int $idNivel): void
    {
        $usuario = $this->exigirInstructor();

        if ($idNivel <= 0) {
            $this->respuesta(400, [
                'exito' => false,
                'mensaje' => 'El ID del nivel no es válido.'
            ]);
        }

        try {
            $contenidos = $this->nivel->consultarContenidos(
                $idNivel,
                $usuario['id']
            );

            $this->respuesta(200, [
                'exito' => true,
                'contenidos' => $contenidos
            ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());

            $this->respuesta(404, [
                'exito' => false,
                'mensaje' => 'No se pudieron consultar los contenidos de este nivel.'
            ]);
        }
    }

    public function modificarContenido(int $idContenido): void
    {
        $usuario = $this->exigirInstructor();

        if ($idContenido <= 0) {
            $this->respuesta(400, [
                'exito' => false,
                'mensaje' => 'El ID del contenido no es válido.'
            ]);
        }

        $datos = $this->validarContenido($this->leerDatos());

        try {
            $this->nivel->modificarContenido(
                $idContenido,
                $usuario['id'],
                $datos['tipo'],
                $datos['titulo'],
                $datos['descripcion'],
                $datos['ruta_archivo'],
                $datos['url_externa'],
                $datos['texto_contenido'],
                $datos['orden']
            );

            $this->respuesta(200, [
                'exito' => true,
                'mensaje' => 'Contenido actualizado correctamente.'
            ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());

            $this->respuesta(422, [
                'exito' => false,
                'mensaje' => 'No se pudo modificar el contenido.'
            ]);
        }
    }

    public function eliminarContenido(int $idContenido): void
    {
        $usuario = $this->exigirInstructor();

        if ($idContenido <= 0) {
            $this->respuesta(400, [
                'exito' => false,
                'mensaje' => 'El ID del contenido no es válido.'
            ]);
        }

        try {
            $this->nivel->eliminarContenido(
                $idContenido,
                $usuario['id']
            );

            $this->respuesta(200, [
                'exito' => true,
                'mensaje' => 'Contenido eliminado correctamente.'
            ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());

            $this->respuesta(404, [
                'exito' => false,
                'mensaje' => 'No se pudo eliminar el contenido.'
            ]);
        }
    }
}
