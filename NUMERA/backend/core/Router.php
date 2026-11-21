
<?php

class Router
{
    private array $rutas = [];

    public function get(string $ruta, callable $accion): void
    {
        $this->agregar('GET', $ruta, $accion);
    }

    public function post(string $ruta, callable $accion): void
    {
        $this->agregar('POST', $ruta, $accion);
    }

    public function put(string $ruta, callable $accion): void
    {
        $this->agregar('PUT', $ruta, $accion);
    }

    public function patch(string $ruta, callable $accion): void
    {
        $this->agregar('PATCH', $ruta, $accion);
    }

    public function delete(string $ruta, callable $accion): void
    {
        $this->agregar('DELETE', $ruta, $accion);
    }

    private function agregar(
        string $metodo,
        string $ruta,
        callable $accion
    ): void {
        $this->rutas[$metodo][$ruta] = $accion;
    }

    public function ejecutar(): void
    {
        $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $uri = parse_url(
            $_SERVER['REQUEST_URI'] ?? '/',
            PHP_URL_PATH
        );

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        $rutaSolicitada = '/' . trim($uri, '/');

        if ($rutaSolicitada === '//') {
            $rutaSolicitada = '/';
        }

        $rutasMetodo = $this->rutas[$metodo] ?? [];

        foreach ($rutasMetodo as $ruta => $accion) {
            $patron = preg_quote($ruta, '~');

            $patron = preg_replace_callback(
                '/\\\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\\\}/',
                function ($coincidencia) {
                    return '(?P<' . $coincidencia[1] . '>[^/]+)';
                },
                $patron
            );

            if (preg_match('~^' . $patron . '$~', $rutaSolicitada, $coincidencias)) {
                $parametros = [];

                foreach ($coincidencias as $clave => $valor) {
                    if (is_string($clave)) {
                        $parametros[] = $valor;
                    }
                }

                call_user_func_array($accion, $parametros);
                return;
            }
        }

        Response::json([
            'exito' => false,
            'mensaje' => 'Ruta o método HTTP no encontrado.'
        ], 404);
    }
}
