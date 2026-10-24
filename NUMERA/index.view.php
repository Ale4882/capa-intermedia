<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NUMERA | Aprende Matemáticas</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cascadia+Code:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <header class="header">
        <div class="logo">
            <span class="logo-symbol">√</span>
            <span>NUMERA</span>
        </div>
        <div class="header-actions">
            <button class="theme-button" id="themeButton" title="Cambiar tema">☾</button>
            <a href="login.php" class="login-button">Iniciar sesión</a>
            <a href="registro.php" class="register-button">Registrarse</a>
        </div>
    </header>

    <main>
        <!-- HERO -->
        <section class="hero">
            <div class="hero-text">
                <span class="small-label">APRENDE • RESUELVE • AVANZA</span>
                <h1>Las matemáticas <span>tienen sentido.</span></h1>
                <p>Explora cursos de matemáticas y ciencias, aprende a tu propio ritmo y lleva tu progreso contigo.</p>
                <div class="search-box">
                    <span class="search-icon">⌕</span>
                    <input type="text" placeholder="¿Qué quieres aprender?" id="searchInput">
                    <button id="searchButton">→</button>
                </div>
            </div>
            <div class="hero-decoration">
                <div class="math-display">
                    <div class="display-top">NUMERA</div>
                    <div class="display-equation">x² + y² = r²</div>
                    <div class="display-result">= 1</div>
                </div>
                <div class="math-symbol symbol-one">π</div>
                <div class="math-symbol symbol-two">∫</div>
                <div class="math-symbol symbol-three">√</div>
                <div class="cat">/\_/\<br>( •ᴗ• )<br>/ > 📐</div>
            </div>
        </section>

        <!-- CATEGORÍAS -->
        <section class="section">
            <div class="section-heading">
                <div>
                    <span class="section-label">EXPLORA</span>
                    <h2>Categorías</h2>
                </div>
                <a href="login.php" class="see-all">Ver todas →</a>
            </div>
            <div class="categories">
                <?php foreach ($categories as $cat): ?>
                    <a href="login.php" class="category-card <?= $cat['class']; ?>">
                        <div class="category-symbol"><?= $cat['symbol']; ?></div>
                        <div>
                            <h3><?= $cat['title']; ?></h3>
                            <p><?= $cat['desc']; ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- FUNCIÓN AUXILIAR PARA RENDERIZAR GRILLAS DE CURSOS -->
        <?php function renderCourseGrid($title, $label, $courses) { ?>
            <section class="section">
                <div class="section-heading">
                    <div>
                        <span class="section-label"><?= $label; ?></span>
                        <h2><?= $title; ?></h2>
                    </div>
                    <a href="#" class="see-all">Ver todos →</a>
                </div>
                <div class="courses-grid">
                    <?php foreach ($courses as $course): ?>
                        <article class="course-card">
                            <div class="course-image <?= $course['image_class']; ?>">
                                <?= $course['symbol']; ?>
                            </div>
                            <div class="course-content">
                                <span class="course-category"><?= $course['category']; ?></span>
                                <h3><?= $course['title']; ?></h3>
                                <p class="instructor"><?= $course['instructor']; ?></p>
                                <div class="course-info">
                                    <span class="rating"><?= $course['rating']; ?></span>
                                    <span class="levels"><?= $course['levels']; ?></span>
                                </div>
                                <div class="course-footer">
                                    <strong><?= $course['price']; ?></strong>
                                    <button class="course-button">→</button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php } ?>

        <!-- CURSOS MEJOR CALIFICADOS -->
        <?php renderCourseGrid('Mejor calificados', 'DESTACADOS', $topCourses); ?>

        <!-- MÁS VENDIDOS -->
        <?php renderCourseGrid('Más vendidos', 'POPULARES', $bestSellers); ?>

        <!-- CURSOS RECIENTES -->
        <?php renderCourseGrid('Recién publicados', 'NUEVOS', $recentCourses); ?>

    </main>

    <footer class="footer">
        <div class="footer-logo">√ NUMERA</div>
        <p>Aprende. Resuelve. Avanza.</p>
        <span>© 2026 NUMERA</span>
    </footer>

    <script src="html/js/script.js"></script>
</body>
</html>