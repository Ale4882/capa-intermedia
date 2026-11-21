
USE numera;

DELIMITER $$

-- CREAR CURSO
DROP PROCEDURE IF EXISTS SP_CrearCurso$$
CREATE PROCEDURE SP_CrearCurso(
    IN p_id_instructor INT,
    IN p_titulo VARCHAR(255),
    IN p_descripcion TEXT,
    IN p_imagen VARCHAR(255),
    IN p_precio DECIMAL(10,2),
    IN p_precio_por_nivel TINYINT
)
BEGIN
    INSERT INTO cursos(
        id_instructor,
        titulo,
        descripcion,
        imagen,
        precio,
        precio_por_nivel,
        publicado,
        eliminado,
        fecha_creacion,
        fecha_actualizacion
    )
    VALUES(
        p_id_instructor,
        p_titulo,
        p_descripcion,
        p_imagen,
        p_precio,
        p_precio_por_nivel,
        0,
        0,
        NOW(),
        NOW()
    );

    SELECT LAST_INSERT_ID() AS id_curso;
END$$


-- CONSULTAR CURSOS DE UN INSTRUCTOR
DROP PROCEDURE IF EXISTS SP_CursosInstructor$$
CREATE PROCEDURE SP_CursosInstructor(
    IN p_id_instructor INT
)
BEGIN
    SELECT
        id_curso,
        id_instructor,
        titulo,
        descripcion,
        imagen,
        precio,
        precio_por_nivel,
        publicado,
        eliminado,
        fecha_creacion,
        fecha_actualizacion
    FROM cursos
    WHERE id_instructor = p_id_instructor
      AND eliminado = 0
    ORDER BY fecha_creacion DESC;
END$$


-- CONSULTAR CATÁLOGO PÚBLICO
DROP PROCEDURE IF EXISTS SP_CatalogoCursos$$
CREATE PROCEDURE SP_CatalogoCursos()
BEGIN
    SELECT
        c.id_curso,
        c.id_instructor,
        c.titulo,
        c.descripcion,
        c.imagen,
        c.precio,
        c.precio_por_nivel,
        c.fecha_publicacion,
        u.nombre_completo AS instructor
    FROM cursos c
    INNER JOIN usuarios u
        ON u.id_usuario = c.id_instructor
    WHERE c.publicado = 1
      AND c.eliminado = 0
      AND u.activo = 1
      AND u.bloqueado = 0
    ORDER BY c.fecha_publicacion DESC;
END$$


-- CONSULTAR UN CURSO
DROP PROCEDURE IF EXISTS SP_ConsultarCurso$$
CREATE PROCEDURE SP_ConsultarCurso(
    IN p_id_curso INT
)
BEGIN
    SELECT
        c.id_curso,
        c.id_instructor,
        c.titulo,
        c.descripcion,
        c.imagen,
        c.precio,
        c.precio_por_nivel,
        c.publicado,
        c.eliminado,
        c.fecha_publicacion,
        c.fecha_creacion,
        c.fecha_actualizacion,
        u.nombre_completo AS instructor
    FROM cursos c
    INNER JOIN usuarios u
        ON u.id_usuario = c.id_instructor
    WHERE c.id_curso = p_id_curso
      AND c.eliminado = 0;
END$$


-- MODIFICAR CURSO
DROP PROCEDURE IF EXISTS SP_ModificarCurso$$
CREATE PROCEDURE SP_ModificarCurso(
    IN p_id_curso INT,
    IN p_id_instructor INT,
    IN p_titulo VARCHAR(255),
    IN p_descripcion TEXT,
    IN p_imagen VARCHAR(255),
    IN p_precio DECIMAL(10,2),
    IN p_precio_por_nivel TINYINT
)
BEGIN
    UPDATE cursos
    SET
        titulo = p_titulo,
        descripcion = p_descripcion,
        imagen = p_imagen,
        precio = p_precio,
        precio_por_nivel = p_precio_por_nivel,
        fecha_actualizacion = NOW()
    WHERE id_curso = p_id_curso
      AND id_instructor = p_id_instructor
      AND eliminado = 0;

    SELECT ROW_COUNT() AS afectados;
END$$


-- PUBLICAR / DESPUBLICAR CURSO
DROP PROCEDURE IF EXISTS SP_CambiarEstadoCurso$$
CREATE PROCEDURE SP_CambiarEstadoCurso(
    IN p_id_curso INT,
    IN p_id_instructor INT,
    IN p_publicado TINYINT
)
BEGIN
    UPDATE cursos
    SET
        publicado = p_publicado,
        fecha_publicacion = CASE
            WHEN p_publicado = 1 THEN NOW()
            ELSE fecha_publicacion
        END,
        fecha_actualizacion = NOW()
    WHERE id_curso = p_id_curso
      AND id_instructor = p_id_instructor
      AND eliminado = 0;

    SELECT ROW_COUNT() AS afectados;
END$$


-- ELIMINACIÓN LÓGICA
DROP PROCEDURE IF EXISTS SP_EliminarCurso$$
CREATE PROCEDURE SP_EliminarCurso(
    IN p_id_curso INT,
    IN p_id_instructor INT
)
BEGIN
    UPDATE cursos
    SET
        eliminado = 1,
        publicado = 0,
        fecha_actualizacion = NOW()
    WHERE id_curso = p_id_curso
      AND id_instructor = p_id_instructor
      AND eliminado = 0;

    SELECT ROW_COUNT() AS afectados;
END$$

DELIMITER ;
