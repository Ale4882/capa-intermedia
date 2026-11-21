
<?php

class Request
{
    public static function json(): array
    {
        $contenido = file_get_contents('php://input');

        if ($contenido === false || trim($contenido) === '') {
            Response::json([
                'exito' => false,
                'mensaje' => 'El cuerpo de la solicitud está vacío.'
            ], 400);
        }

        $datos = json_decode($contenido, true);

        if (!is_array($datos)) {
            Response::json([
                'exito' => false,
                'mensaje' => 'El formato JSON no es válido.'
            ], 400);
        }

        return $datos;
    }
}
