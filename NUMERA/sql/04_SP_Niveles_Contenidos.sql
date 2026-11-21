
-- ============================================
-- NUMERA
-- PROCEDIMIENTOS ALMACENADOS: NIVELES Y CONTENIDOS
-- ============================================

DELIMITER $$

-- ============================================
-- 1. CREAR NIVEL
-- ============================================

DROP PROCEDURE IF EXISTS SP_CrearNivel$$

CREATE PROCEDURE SP_CrearNivel(
    IN p_id_curso INT,
    IN p_id_instructor INT,
    IN p_titulo VARCHAR(150),
    IN p_descripcion TEXT,
    IN p_orden INT,
    IN p_precio DECIMAL(10,2)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM cursos
        WHERE id_curso = p_id_curso
          AND id_instructor = p_id_instructor
          AND eliminado = 0
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El curso no existe o no pertenece al instructor.';
    END IF;

    IF TRIM(p_titulo) = '' OR p_orden <= 0 OR p_precio < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Los datos del nivel no son válidos.';
    END IF;

    INSERT INTO niveles (
        id_curso,
        titulo,
        descripcion,
        orden,
        precio,
        activo
    )
    VALUES (
        p_id_curso,
        p_titulo,
        p_descripcion,
        p_orden,
        p_precio,
        1
    );

    SELECT LAST_INSERT_ID() AS id_nivel;
END$$


-- ============================================
-- 2. CONSULTAR NIVELES DE UN CURSO
-- ============================================

DROP PROCEDURE IF EXISTS SP_NivelesCurso$$

CREATE PROCEDURE SP_NivelesCurso(
    IN p_id_curso INT,
    IN p_id_instructor INT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM cursos
        WHERE id_curso = p_id_curso
          AND id_instructor = p_id_instructor
          AND eliminado = 0
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El curso no existe o no pertenece al instructor.';
    END IF;

    SELECT
        id_nivel,
        id_curso,
        titulo,
        descripcion,
        orden,
        precio,
        activo
    FROM niveles
    WHERE id_curso = p_id_curso
    ORDER BY orden ASC;
END$$


-- ============================================
-- 3. MODIFICAR NIVEL
-- ============================================

DROP PROCEDURE IF EXISTS SP_ModificarNivel$$

CREATE PROCEDURE SP_ModificarNivel(
    IN p_id_nivel INT,
    IN p_id_instructor INT,
    IN p_titulo VARCHAR(150),
    IN p_descripcion TEXT,
    IN p_orden INT,
    IN p_precio DECIMAL(10,2)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM niveles n
        INNER JOIN cursos c ON c.id_curso = n.id_curso
        WHERE n.id_nivel = p_id_nivel
          AND c.id_instructor = p_id_instructor
          AND c.eliminado = 0
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El nivel no existe o no pertenece al instructor.';
    END IF;

    IF TRIM(p_titulo) = '' OR p_orden <= 0 OR p_precio < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Los datos del nivel no son válidos.';
    END IF;

    UPDATE niveles
    SET
        titulo = p_titulo,
        descripcion = p_descripcion,
        orden = p_orden,
        precio = p_precio
    WHERE id_nivel = p_id_nivel;

    SELECT 1 AS exito;
END$$


-- ============================================
-- 4. CAMBIAR ESTADO DEL NIVEL
-- ============================================

DROP PROCEDURE IF EXISTS SP_CambiarEstadoNivel$$

CREATE PROCEDURE SP_CambiarEstadoNivel(
    IN p_id_nivel INT,
    IN p_id_instructor INT,
    IN p_activo TINYINT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM niveles n
        INNER JOIN cursos c ON c.id_curso = n.id_curso
        WHERE n.id_nivel = p_id_nivel
          AND c.id_instructor = p_id_instructor
          AND c.eliminado = 0
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El nivel no existe o no pertenece al instructor.';
    END IF;

    IF p_activo NOT IN (0, 1) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El estado del nivel no es válido.';
    END IF;

    UPDATE niveles
    SET activo = p_activo
    WHERE id_nivel = p_id_nivel;

    SELECT 1 AS exito;
END$$


-- ============================================
-- 5. AGREGAR CONTENIDO A UN NIVEL
-- ============================================

DROP PROCEDURE IF EXISTS SP_CrearContenidoNivel$$

CREATE PROCEDURE SP_CrearContenidoNivel(
    IN p_id_nivel INT,
    IN p_id_instructor INT,
    IN p_tipo VARCHAR(20),
    IN p_titulo VARCHAR(150),
    IN p_descripcion TEXT,
    IN p_ruta_archivo VARCHAR(255),
    IN p_url_externa VARCHAR(500),
    IN p_texto_contenido LONGTEXT,
    IN p_orden INT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM niveles n
        INNER JOIN cursos c ON c.id_curso = n.id_curso
        WHERE n.id_nivel = p_id_nivel
          AND c.id_instructor = p_id_instructor
          AND c.eliminado = 0
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El nivel no existe o no pertenece al instructor.';
    END IF;

    IF p_tipo NOT IN ('video', 'texto', 'pdf', 'imagen', 'enlace') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El tipo de contenido no es válido.';
    END IF;

    IF TRIM(p_titulo) = '' OR p_orden <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El título o el orden del contenido no son válidos.';
    END IF;

    INSERT INTO contenido_nivel (
        id_nivel,
        tipo,
        titulo,
        descripcion,
        ruta_archivo,
        url_externa,
        texto_contenido,
        orden
    )
    VALUES (
        p_id_nivel,
        p_tipo,
        p_titulo,
        p_descripcion,
        p_ruta_archivo,
        p_url_externa,
        p_texto_contenido,
        p_orden
    );

    SELECT LAST_INSERT_ID() AS id_contenido;
END$$


-- ============================================
-- 6. CONSULTAR CONTENIDO DE UN NIVEL
-- ============================================

DROP PROCEDURE IF EXISTS SP_ContenidosNivel$$

CREATE PROCEDURE SP_ContenidosNivel(
    IN p_id_nivel INT,
    IN p_id_instructor INT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM niveles n
        INNER JOIN cursos c ON c.id_curso = n.id_curso
        WHERE n.id_nivel = p_id_nivel
          AND c.id_instructor = p_id_instructor
          AND c.eliminado = 0
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El nivel no existe o no pertenece al instructor.';
    END IF;

    SELECT
        id_contenido,
        id_nivel,
        tipo,
        titulo,
        descripcion,
        ruta_archivo,
        url_externa,
        texto_contenido,
        orden,
        fecha_creacion
    FROM contenido_nivel
    WHERE id_nivel = p_id_nivel
    ORDER BY orden ASC, id_contenido ASC;
END$$


-- ============================================
-- 7. MODIFICAR CONTENIDO
-- ============================================

DROP PROCEDURE IF EXISTS SP_ModificarContenidoNivel$$

CREATE PROCEDURE SP_ModificarContenidoNivel(
    IN p_id_contenido INT,
    IN p_id_instructor INT,
    IN p_tipo VARCHAR(20),
    IN p_titulo VARCHAR(150),
    IN p_descripcion TEXT,
    IN p_ruta_archivo VARCHAR(255),
    IN p_url_externa VARCHAR(500),
    IN p_texto_contenido LONGTEXT,
    IN p_orden INT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM contenido_nivel cn
        INNER JOIN niveles n ON n.id_nivel = cn.id_nivel
        INNER JOIN cursos c ON c.id_curso = n.id_curso
        WHERE cn.id_contenido = p_id_contenido
          AND c.id_instructor = p_id_instructor
          AND c.eliminado = 0
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El contenido no existe o no pertenece al instructor.';
    END IF;

    IF p_tipo NOT IN ('video', 'texto', 'pdf', 'imagen', 'enlace') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El tipo de contenido no es válido.';
    END IF;

    IF TRIM(p_titulo) = '' OR p_orden <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El título o el orden del contenido no son válidos.';
    END IF;

    UPDATE contenido_nivel
    SET
        tipo = p_tipo,
        titulo = p_titulo,
        descripcion = p_descripcion,
        ruta_archivo = p_ruta_archivo,
        url_externa = p_url_externa,
        texto_contenido = p_texto_contenido,
        orden = p_orden
    WHERE id_contenido = p_id_contenido;

    SELECT 1 AS exito;
END$$


-- ============================================
-- 8. ELIMINAR CONTENIDO
-- ============================================

DROP PROCEDURE IF EXISTS SP_EliminarContenidoNivel$$

CREATE PROCEDURE SP_EliminarContenidoNivel(
    IN p_id_contenido INT,
    IN p_id_instructor INT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM contenido_nivel cn
        INNER JOIN niveles n ON n.id_nivel = cn.id_nivel
        INNER JOIN cursos c ON c.id_curso = n.id_curso
        WHERE cn.id_contenido = p_id_contenido
          AND c.id_instructor = p_id_instructor
          AND c.eliminado = 0
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El contenido no existe o no pertenece al instructor.';
    END IF;

    DELETE FROM contenido_nivel
    WHERE id_contenido = p_id_contenido;

    SELECT 1 AS exito;
END$$

DELIMITER ;
