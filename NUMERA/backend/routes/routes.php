
<?php

require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../controllers/HealthController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/CursoController.php';

require_once __DIR__ . '/../models/Curso.php';

require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../core/Response.php';

require_once __DIR__ . '/../middleware/AuthMiddleware.php';

require_once __DIR__ . '/../models/Nivel.php';
require_once __DIR__ . '/../controllers/NivelController.php';

require_once __DIR__ . '/../models/Inscripcion.php';
require_once __DIR__ . '/../controllers/InscripcionController.php';

// Conexión e inyección de dependencias
$db = Database::conectar();

$healthController = new HealthController();
$authController = new AuthController();

$cursoModel = new Curso($db);
$cursoController = new CursoController($cursoModel);

$nivelModel = new Nivel($db);
$nivelController = new NivelController($nivelModel);

$inscripcionModel = new Inscripcion($db);
$inscripcionController = new InscripcionController($inscripcionModel);

// Rutas públicas
$router->get('/health', [$healthController, 'comprobar']);

$router->post('/auth/registro', [$authController, 'registrar']);
$router->post('/auth/login', [$authController, 'iniciarSesion']);

// Rutas de sesión
$router->get('/auth/sesion', [$authController, 'consultarSesion']);
$router->post('/auth/logout', [$authController, 'cerrarSesion']);

// Ruta protegida: requiere iniciar sesión
$router->get('/auth/privada', function () {
    AuthMiddleware::autenticar();

    Response::json([
        'exito' => true,
        'mensaje' => 'Acceso autorizado. Tienes una sesión activa.'
    ]);
});

// Ruta protegida: requiere rol de administrador
$router->get('/auth/admin', function () {
    AuthMiddleware::permitirRoles(['administrador']);

    Response::json([
        'exito' => true,
        'mensaje' => 'Acceso autorizado para administrador.'
    ]);
});

// ======================================
// MÓDULO DE CURSOS
// ======================================

// Catálogo público de cursos
$router->get('/cursos', [$cursoController, 'catalogo']);

// Detalle de un curso
$router->get('/cursos/{id}', [$cursoController, 'consultar']);

// Cursos del profesor autenticado
$router->get('/profesor/cursos', [$cursoController, 'misCursos']);

// Crear curso
$router->post('/cursos', [$cursoController, 'crear']);

// Modificar curso
$router->put('/cursos/{id}', [$cursoController, 'modificar']);

// Publicar o despublicar curso
$router->patch('/cursos/{id}/estado', [$cursoController, 'estado']);

// Eliminación lógica del curso
$router->delete('/cursos/{id}', [$cursoController, 'eliminar']);


// ======================================
// MÓDULO DE NIVELES
// ======================================

// Consultar niveles de un curso
$router->get(
    '/cursos/{id}/niveles',
    [$nivelController, 'consultarPorCurso']
);

// Crear nivel dentro de un curso
$router->post(
    '/cursos/{id}/niveles',
    [$nivelController, 'crear']
);

// Modificar nivel
$router->put(
    '/niveles/{id}',
    [$nivelController, 'modificar']
);

// Activar o desactivar nivel
$router->patch(
    '/niveles/{id}/estado',
    [$nivelController, 'estado']
);

// ======================================
// MÓDULO DE CONTENIDOS
// ======================================

// Consultar contenidos de un nivel
$router->get(
    '/niveles/{id}/contenidos',
    [$nivelController, 'consultarContenidos']
);

// Agregar contenido a un nivel
$router->post(
    '/niveles/{id}/contenidos',
    [$nivelController, 'crearContenido']
);

// Modificar contenido
$router->put(
    '/contenidos/{id}',
    [$nivelController, 'modificarContenido']
);

// Eliminar contenido
$router->delete(
    '/contenidos/{id}',
    [$nivelController, 'eliminarContenido']
);


// INSCRIPCIONES Y ACCESO DE ESTUDIANTES

$router->get(
    '/estudiante/cursos',
    [$inscripcionController, 'misCursos']
);

$router->post(
    '/estudiante/cursos/{id}/inscripcion',
    [$inscripcionController, 'inscribirCurso']
);

$router->post(
    '/estudiante/niveles/{id}/inscripcion',
    [$inscripcionController, 'inscribirNivel']
);

$router->get(
    '/estudiante/niveles/{id}/acceso',
    [$inscripcionController, 'validarAcceso']
);

$router->get(
    '/estudiante/niveles/{id}/contenidos',
    [$inscripcionController, 'contenidos']
);

$router->patch(
    '/estudiante/niveles/{id}/progreso',
    [$inscripcionController, 'actualizarProgreso']
);

$router->get(
    '/estudiante/cursos/{id}/progreso',
    [$inscripcionController, 'progresoCurso']
);