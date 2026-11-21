
<?php

class InscripcionController extends Controller
{
    private Inscripcion $inscripcion;

    public function __construct(Inscripcion $inscripcion)
    {
        $this->inscripcion = $inscripcion;
    }

    private function estudianteActual(): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (
            !isset($_SESSION['usuario']['id_usuario']) ||
            !isset($_SESSION['usuario']['rol'])
        ) {
            $this->responder([
                'exito' => false,
                'mensaje' => 'Debes iniciar sesión.'
            ], 401);

            exit;
        }

        if ($_SESSION['usuario']['rol'] !== 'estudiante') {
            $this->responder([
                'exito' => false,
                'mensaje' => 'Esta operación es exclusiva para estudiantes.'
            ], 403);

            exit;
        }

        return (int) $_SESSION['usuario']['id_usuario'];
    }

    private function leerDatos(): array
    {
        $datos = json_decode(file_get_contents('php://input'), true);

        if (!is_array($datos)) {
            $this->responder([
                'exito' => false,
                'mensaje' => 'El cuerpo JSON es inválido.'
            ], 400);

            exit;
        }

        return $datos;
    }

    private function manejarError(Throwable $e, string $mensaje): void
    {
        error_log($e->getMessage());

        $this->responder([
            'exito' => false,
            'mensaje' => $mensaje
        ], 422);
    }

    public function inscribirCurso(int $idCurso): void
    {
        $idUsuario = $this->estudianteActual();

        try {
            $resultado = $this->inscripcion->inscribirCursoGratis(
                $idUsuario,
                $idCurso
            );

            $this->responder([
                'exito' => true,
                'mensaje' => $resultado[0]['mensaje'] ??
                    'Inscripción realizada correctamente.'
            ], 201);

        } catch (Throwable $e) {
            $this->manejarError(
                $e,
                'No se pudo realizar la inscripción al curso.'
            );
        }
    }

    public function inscribirNivel(int $idNivel): void
    {
        $idUsuario = $this->estudianteActual();

        try {
            $resultado = $this->inscripcion->inscribirNivelGratis(
                $idUsuario,
                $idNivel
            );

            $this->responder([
                'exito' => true,
                'mensaje' => $resultado[0]['mensaje'] ??
                    'Inscripción al nivel realizada correctamente.'
            ], 201);

        } catch (Throwable $e) {
            $this->manejarError(
                $e,
                'No se pudo realizar la inscripción al nivel.'
            );
        }
    }

    public function misCursos(): void
    {
        $idUsuario = $this->estudianteActual();

        try {
            $cursos = $this->inscripcion->misCursos($idUsuario);

            $this->responder([
                'exito' => true,
                'cursos' => $cursos
            ]);

        } catch (Throwable $e) {
            $this->manejarError(
                $e,
                'No se pudieron consultar tus cursos.'
            );
        }
    }

    public function validarAcceso(int $idNivel): void
    {
        $idUsuario = $this->estudianteActual();

        try {
            $resultado = $this->inscripcion->validarAcceso(
                $idUsuario,
                $idNivel
            );

            if (
                empty($resultado) ||
                (int) $resultado[0]['tiene_acceso'] !== 1
            ) {
                $this->responder([
                    'exito' => false,
                    'mensaje' => 'No tienes acceso a este nivel.'
                ], 403);

                return;
            }

            $this->responder([
                'exito' => true,
                'mensaje' => 'Tienes acceso a este nivel.',
                'nivel' => $resultado[0]
            ]);

        } catch (Throwable $e) {
            $this->manejarError(
                $e,
                'No se pudo verificar el acceso al nivel.'
            );
        }
    }

    public function contenidos(int $idNivel): void
    {
        $idUsuario = $this->estudianteActual();

        try {
            $contenidos = $this->inscripcion->contenidosEstudiante(
                $idUsuario,
                $idNivel
            );

            $this->responder([
                'exito' => true,
                'contenidos' => $contenidos
            ]);

        } catch (Throwable $e) {
            $this->manejarError(
                $e,
                'No se pudieron consultar los contenidos. Verifica que tengas acceso al nivel.'
            );
        }
    }

    public function actualizarProgreso(int $idNivel): void
    {
        $idUsuario = $this->estudianteActual();
        $datos = $this->leerDatos();

        if (
            !isset($datos['porcentaje']) ||
            !is_numeric($datos['porcentaje'])
        ) {
            $this->responder([
                'exito' => false,
                'mensaje' => 'Debes proporcionar un porcentaje válido.'
            ], 400);

            return;
        }

        $porcentaje = (float) $datos['porcentaje'];

        if ($porcentaje < 0 || $porcentaje > 100) {
            $this->responder([
                'exito' => false,
                'mensaje' => 'El porcentaje debe estar entre 0 y 100.'
            ], 400);

            return;
        }

        try {
            $resultado = $this->inscripcion->actualizarProgreso(
                $idUsuario,
                $idNivel,
                $porcentaje
            );

            $this->responder([
                'exito' => true,
                'mensaje' => $resultado[0]['mensaje'] ??
                    'Progreso actualizado correctamente.'
            ]);

        } catch (Throwable $e) {
            $this->manejarError(
                $e,
                'No se pudo actualizar el progreso. Verifica que tengas acceso al nivel.'
            );
        }
    }

    public function progresoCurso(int $idCurso): void
    {
        $idUsuario = $this->estudianteActual();

        try {
            $progreso = $this->inscripcion->progresoCurso(
                $idUsuario,
                $idCurso
            );

            $this->responder([
                'exito' => true,
                'progreso' => $progreso
            ]);

        } catch (Throwable $e) {
            $this->manejarError(
                $e,
                'No se pudo consultar el progreso del curso.'
            );
        }
    }
}
