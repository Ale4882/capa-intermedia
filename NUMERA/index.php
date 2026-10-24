<?php
require_once __DIR__ . '/../models/Cursos.php';

class Index {
    public function index() {
        $model = new Curso();
        
        // Obtener datos
        $categories = $model->getCategories();
        $topCourses = $model->getTopRatedCourses();
        $bestSellers = $model->getBestSellers();
        $recentCourses = $model->getRecentCourses();

        // Cargar la vista y enviarle las variables
        require_once __DIR__ . '/../views/index.view.php';
    }
}