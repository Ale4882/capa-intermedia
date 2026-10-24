<?php
class Curso {
    public function getCategories() {
        return [
            ['title' => 'Álgebra', 'desc' => 'Ecuaciones y expresiones', 'symbol' => 'x²', 'class' => 'category-blue'],
            ['title' => 'Cálculo', 'desc' => 'Límites y derivadas', 'symbol' => '∫', 'class' => 'category-purple'],
            ['title' => 'Geometría', 'desc' => 'Figuras y dimensiones', 'symbol' => '△', 'class' => 'category-green'],
            ['title' => 'Trigonometría', 'desc' => 'Ángulos y funciones', 'symbol' => 'sin θ', 'class' => 'category-red'],
            ['title' => 'Estadística', 'desc' => 'Datos y probabilidades', 'symbol' => 'σ', 'class' => 'category-yellow'],
            ['title' => 'Física', 'desc' => 'Movimiento y energía', 'symbol' => 'F=ma', 'class' => 'category-orange']
        ];
    }

    public function getTopRatedCourses() {
        return [
            [
                'category' => 'CÁLCULO',
                'title' => 'Cálculo diferencial desde cero',
                'instructor' => 'Por Alejandro Torres',
                'rating' => '★ 4.9',
                'levels' => '5 niveles',
                'price' => '$299.00',
                'image_class' => 'course-image-blue',
                'symbol' => '∫'
            ],
            [
                'category' => 'ÁLGEBRA',
                'title' => 'Álgebra fundamental',
                'instructor' => 'Por María González',
                'rating' => '★ 4.8',
                'levels' => '4 niveles',
                'price' => '$249.00',
                'image_class' => 'course-image-purple',
                'symbol' => 'x²'
            ],
            [
                'category' => 'GEOMETRÍA',
                'title' => 'Geometría para principiantes',
                'instructor' => 'Por Carlos Ramírez',
                'rating' => '★ 4.8',
                'levels' => '6 niveles',
                'price' => '$199.00',
                'image_class' => 'course-image-green',
                'symbol' => '△'
            ]
        ];
    }

    public function getBestSellers() {
        return [
            [
                'category' => 'TRIGONOMETRÍA',
                'title' => 'Trigonometría esencial',
                'instructor' => 'Por Laura Martínez',
                'rating' => '★ 4.7',
                'levels' => '5 niveles',
                'price' => '$279.00',
                'image_class' => 'course-image-red',
                'symbol' => 'sin θ'
            ],
            [
                'category' => 'ESTADÍSTICA',
                'title' => 'Estadística básica',
                'instructor' => 'Por Daniel López',
                'rating' => '★ 4.6',
                'levels' => '4 niveles',
                'price' => '$229.00',
                'image_class' => 'course-image-yellow',
                'symbol' => 'σ'
            ],
            [
                'category' => 'FÍSICA',
                'title' => 'Física básica',
                'instructor' => 'Por Ana Pérez',
                'rating' => '★ 4.7',
                'levels' => '7 niveles',
                'price' => '$329.00',
                'image_class' => 'course-image-orange',
                'symbol' => 'F=ma'
            ]
        ];
    }

    public function getRecentCourses() {
        return [
            [
                'category' => 'MATEMÁTICAS',
                'title' => 'Matemáticas desde cero',
                'instructor' => 'Por Sofía Hernández',
                'rating' => '★ Nuevo',
                'levels' => '8 niveles',
                'price' => '$259.00',
                'image_class' => 'course-image-blue',
                'symbol' => 'π'
            ],
            [
                'category' => 'MATEMÁTICAS',
                'title' => 'Problemas matemáticos',
                'instructor' => 'Por Luis Castillo',
                'rating' => '★ Nuevo',
                'levels' => '3 niveles',
                'price' => 'GRATIS',
                'image_class' => 'course-image-purple',
                'symbol' => '√'
            ],
            [
                'category' => 'ESTADÍSTICA',
                'title' => 'Introducción a la probabilidad',
                'instructor' => 'Por Andrea Ruiz',
                'rating' => '★ Nuevo',
                'levels' => '4 niveles',
                'price' => '$189.00',
                'image_class' => 'course-image-green',
                'symbol' => '∑'
            ]
        ];
    }
}