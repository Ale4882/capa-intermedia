<?php
$controllerName = isset($_GET['controller']) ? $_GET['controller'] : 'auth';
$actionName = isset($_GET['action']) ? $_GET['action'] : 'showRegisterForm';

if ($controllerName == 'auth') {
    require_once 'controllers/AuthController.php';
    $controller = new AuthController();
    
    if ($actionName == 'register') {
        $controller->register();
    } else {
        // Mostrar la vista por defecto
        require_once 'views/registro.view.php';
    }
}
?>