
<?php

class Controller
{
    protected function responder(
        array $datos,
        int $codigo = 200
    ): void {
        Response::json($datos, $codigo);
    }
}
