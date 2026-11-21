
USE numera;

DELIMITER $$

-- ASOCIAR UNA CATEGORÍA A UN CURSO

DROP PROCEDURE IF EXISTS SP_AgregarCategoriaCurso;

DELIMITER $$

CREATE PROCEDURE SP_AgregarCategoriaCurso(
    IN p_id_curso INT,
    IN p_id_categoria INT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM cursos
        WHERE id_curso = p_id_curso
          AND eliminado = 0
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El curso no existe o fue eliminado';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM categorias
        WHERE id_categoria = p_id_categoria
          AND activa = 1
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La categoría no existe o está inactiva';
    END IF;

    INSERT IGNORE INTO curso_categoria(
        id_curso,
        id_categoria
    )
    VALUES(
        p_id_curso,
        p_id_categoria
    );
END$$

DELIMITER $$



-- ELIMINAR LAS CATEGORÍAS DE UN CURSO
DROP PROCEDURE IF EXISTS SP_LimpiarCategoriasCurso$$

CREATE PROCEDURE SP_LimpiarCategoriasCurso(
    IN p_id_curso INT
)
BEGIN
    DELETE FROM curso_categoria
    WHERE id_curso = p_id_curso;
END$$


-- CONSULTAR LAS CATEGORÍAS DE UN CURSO
DROP PROCEDURE IF EXISTS SP_CategoriasDeCurso$$

CREATE PROCEDURE SP_CategoriasDeCurso(
    IN p_id_curso INT
)
BEGIN
    SELECT
        cat.id_categoria,
        cat.nombre,
        cat.descripcion
    FROM curso_categoria cc
    INNER JOIN categorias cat
        ON cat.id_categoria = cc.id_categoria
    WHERE cc.id_curso = p_id_curso
      AND cat.activa = 1
    ORDER BY cat.nombre;
END$$


-- CONSULTAR CURSOS POR CATEGORÍA
DROP PROCEDURE IF EXISTS SP_CursosPorCategoria$$

CREATE PROCEDURE SP_CursosPorCategoria(
    IN p_id_categoria INT
)
BEGIN
    SELECT
        c.id_curso,
        c.titulo,
        c.descripcion,
        c.imagen,
        c.precio,
        c.precio_por_nivel,
        u.nombre_completo AS instructor,
        cat.nombre AS categoria
    FROM cursos c
    INNER JOIN curso_categoria cc
        ON cc.id_curso = c.id_curso
    INNER JOIN categorias cat
        ON cat.id_categoria = cc.id_categoria
    INNER JOIN usuarios u
        ON u.id_usuario = c.id_instructor
    WHERE cc.id_categoria = p_id_categoria
      AND c.publicado = 1
      AND c.eliminado = 0
      AND cat.activa = 1
      AND u.activo = 1
      AND u.bloqueado = 0
    ORDER BY c.fecha_publicacion DESC;
END$$

DELIMITER ;
