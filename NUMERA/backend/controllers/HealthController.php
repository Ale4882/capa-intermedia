
<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/SistemaModel.php';

class HealthController extends Controller
{
    public function comprobar(): void
    {
        try {
            $modelo = new SistemaModel();

            if ($modelo->comprobarConexion()) {
                $this->responder([
                    'exito' => true,
                    'mensaje' => 'API NUMERA funcionando correctamente.',
                    'base_datos' => 'Conectada'
                ]);
            }

        } catch (Throwable $error) {
            error_log($error->getMessage());

            $this->responder([
                'exito' => false,
                'mensaje' => 'El servicio no está disponible.'
            ], 500);
        }
    }
}
