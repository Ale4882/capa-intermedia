
DROP PROCEDURE IF EXISTS SP_InscribirCursoGratis;
DROP PROCEDURE IF EXISTS SP_InscribirNivelGratis;
DROP PROCEDURE IF EXISTS SP_MisCursosInscritos;
DROP PROCEDURE IF EXISTS SP_ValidarAccesoNivel;
DROP PROCEDURE IF EXISTS SP_ContenidosNivelEstudiante;
DROP PROCEDURE IF EXISTS SP_ActualizarProgresoNivel;
DROP PROCEDURE IF EXISTS SP_ConsultarProgresoCurso;

DELIMITER $$

-- 1. Inscripción gratuita a un curso completo
CREATE PROCEDURE SP_InscribirCursoGratis(
    IN p_id_usuario INT,
    IN p_id_curso INT
)
BEGIN
    DECLARE v_precio DECIMAL(10,2);
    DECLARE v_precio_por_nivel TINYINT;
    DECLARE v_existe INT DEFAULT 0;

    SELECT COUNT(*)
    INTO v_existe
    FROM cursos
    WHERE id_curso = p_id_curso
      AND publicado = 1
      AND eliminado = 0;

    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El curso no existe o no está disponible.';
    END IF;

    SELECT precio, precio_por_nivel
    INTO v_precio, v_precio_por_nivel
    FROM cursos
    WHERE id_curso = p_id_curso;

    IF v_precio > 0 OR v_precio_por_nivel = 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El curso requiere una compra para inscribirse.';
    END IF;

    SELECT COUNT(*)
    INTO v_existe
    FROM inscripciones
    WHERE id_usuario = p_id_usuario
      AND id_curso = p_id_curso
      AND tipo_acceso = 'curso_completo'
      AND estado = 'activa';

    IF v_existe > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Ya estás inscrito en este curso.';
    END IF;

    UPDATE inscripciones
    SET estado = 'activa',
        ultimo_acceso = NOW()
    WHERE id_usuario = p_id_usuario
      AND id_curso = p_id_curso
      AND tipo_acceso = 'curso_completo'
      AND estado = 'cancelada';

    IF ROW_COUNT() = 0 THEN
        INSERT INTO inscripciones (
            id_usuario,
            id_curso,
            tipo_acceso,
            id_nivel,
            fecha_inscripcion,
            estado,
            ultimo_acceso
        )
        VALUES (
            p_id_usuario,
            p_id_curso,
            'curso_completo',
            NULL,
            NOW(),
            'activa',
            NOW()
        );
    END IF;

    SELECT 1 AS exito,
           'Inscripción al curso realizada correctamente.' AS mensaje;
END$$


-- 2. Inscripción gratuita a un nivel
CREATE PROCEDURE SP_InscribirNivelGratis(
    IN p_id_usuario INT,
    IN p_id_nivel INT
)
BEGIN
    DECLARE v_id_curso INT;
    DECLARE v_precio DECIMAL(10,2);
    DECLARE v_precio_por_nivel TINYINT;
    DECLARE v_existe INT DEFAULT 0;

    SELECT COUNT(*)
    INTO v_existe
    FROM niveles n
    INNER JOIN cursos c ON c.id_curso = n.id_curso
    WHERE n.id_nivel = p_id_nivel
      AND n.activo = 1
      AND c.publicado = 1
      AND c.eliminado = 0;

    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El nivel no está disponible.';
    END IF;

    SELECT n.id_curso, n.precio, c.precio_por_nivel
    INTO v_id_curso, v_precio, v_precio_por_nivel
    FROM niveles n
    INNER JOIN cursos c ON c.id_curso = n.id_curso
    WHERE n.id_nivel = p_id_nivel;

    IF v_precio_por_nivel = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Este curso requiere inscripción completa.';
    END IF;

    IF v_precio > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Este nivel requiere una compra.';
    END IF;

    SELECT COUNT(*)
    INTO v_existe
    FROM inscripciones
    WHERE id_usuario = p_id_usuario
      AND id_curso = v_id_curso
      AND tipo_acceso = 'curso_completo'
      AND estado = 'activa';

    IF v_existe > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Ya tienes acceso al curso completo.';
    END IF;

    SELECT COUNT(*)
    INTO v_existe
    FROM inscripciones
    WHERE id_usuario = p_id_usuario
      AND id_curso = v_id_curso
      AND id_nivel = p_id_nivel
      AND tipo_acceso = 'nivel'
      AND estado = 'activa';

    IF v_existe > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Ya estás inscrito en este nivel.';
    END IF;

    UPDATE inscripciones
    SET estado = 'activa',
        ultimo_acceso = NOW()
    WHERE id_usuario = p_id_usuario
      AND id_curso = v_id_curso
      AND id_nivel = p_id_nivel
      AND tipo_acceso = 'nivel'
      AND estado = 'cancelada';

    IF ROW_COUNT() = 0 THEN
        INSERT INTO inscripciones (
            id_usuario,
            id_curso,
            tipo_acceso,
            id_nivel,
            fecha_inscripcion,
            estado,
            ultimo_acceso
        )
        VALUES (
            p_id_usuario,
            v_id_curso,
            'nivel',
            p_id_nivel,
            NOW(),
            'activa',
            NOW()
        );
    END IF;

    SELECT 1 AS exito,
           'Inscripción al nivel realizada correctamente.' AS mensaje;
END$$


-- 3. Consultar cursos inscritos por el estudiante
CREATE PROCEDURE SP_MisCursosInscritos(
    IN p_id_usuario INT
)
BEGIN
    SELECT
        i.id_inscripcion,
        i.id_curso,
        c.titulo,
        c.descripcion,
        c.imagen,
        i.tipo_acceso,
        i.id_nivel,
        i.fecha_inscripcion,
        i.estado,
        i.ultimo_acceso
    FROM inscripciones i
    INNER JOIN cursos c ON c.id_curso = i.id_curso
    WHERE i.id_usuario = p_id_usuario
      AND i.estado = 'activa'
    ORDER BY i.fecha_inscripcion DESC;
END$$


-- 4. Validar acceso de un estudiante a un nivel
CREATE PROCEDURE SP_ValidarAccesoNivel(
    IN p_id_usuario INT,
    IN p_id_nivel INT
)
BEGIN
    SELECT
        n.id_nivel,
        n.id_curso,
        n.titulo,
        n.descripcion,
        n.orden,
        CASE
            WHEN EXISTS (
                SELECT 1
                FROM inscripciones i
                WHERE i.id_usuario = p_id_usuario
                  AND i.id_curso = n.id_curso
                  AND i.estado = 'activa'
                  AND (
                      i.tipo_acceso = 'curso_completo'
                      OR (
                          i.tipo_acceso = 'nivel'
                          AND i.id_nivel = n.id_nivel
                      )
                  )
            )
            THEN 1 ELSE 0
        END AS tiene_acceso
    FROM niveles n
    INNER JOIN cursos c ON c.id_curso = n.id_curso
    WHERE n.id_nivel = p_id_nivel
      AND n.activo = 1
      AND c.publicado = 1
      AND c.eliminado = 0;
END$$


-- 5. Consultar contenidos si el estudiante tiene acceso
CREATE PROCEDURE SP_ContenidosNivelEstudiante(
    IN p_id_usuario INT,
    IN p_id_nivel INT
)
BEGIN
    DECLARE v_acceso INT DEFAULT 0;

    SELECT COUNT(*)
    INTO v_acceso
    FROM niveles n
    INNER JOIN cursos c ON c.id_curso = n.id_curso
    WHERE n.id_nivel = p_id_nivel
      AND n.activo = 1
      AND c.publicado = 1
      AND c.eliminado = 0
      AND EXISTS (
          SELECT 1
          FROM inscripciones i
          WHERE i.id_usuario = p_id_usuario
            AND i.id_curso = n.id_curso
            AND i.estado = 'activa'
            AND (
                i.tipo_acceso = 'curso_completo'
                OR (
                    i.tipo_acceso = 'nivel'
                    AND i.id_nivel = n.id_nivel
                )
            )
      );

    IF v_acceso = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No tienes acceso a este nivel.';
    END IF;

    UPDATE inscripciones i
    INNER JOIN niveles n ON n.id_curso = i.id_curso
    SET i.ultimo_acceso = NOW()
    WHERE n.id_nivel = p_id_nivel
      AND i.id_usuario = p_id_usuario
      AND i.estado = 'activa'
      AND (
          i.tipo_acceso = 'curso_completo'
          OR (i.tipo_acceso = 'nivel' AND i.id_nivel = p_id_nivel)
      );

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
    ORDER BY orden;
END$$


-- 6. Registrar o actualizar progreso
CREATE PROCEDURE SP_ActualizarProgresoNivel(
    IN p_id_usuario INT,
    IN p_id_nivel INT,
    IN p_porcentaje DECIMAL(5,2)
)
BEGIN
    DECLARE v_id_curso INT;
    DECLARE v_id_inscripcion INT;
    DECLARE v_existe INT DEFAULT 0;

    IF p_porcentaje < 0 OR p_porcentaje > 100 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El porcentaje debe estar entre 0 y 100.';
    END IF;

    SELECT n.id_curso
    INTO v_id_curso
    FROM niveles n
    INNER JOIN cursos c ON c.id_curso = n.id_curso
    WHERE n.id_nivel = p_id_nivel
      AND n.activo = 1
      AND c.publicado = 1
      AND c.eliminado = 0;

    SELECT id_inscripcion
    INTO v_id_inscripcion
    FROM inscripciones
    WHERE id_usuario = p_id_usuario
      AND id_curso = v_id_curso
      AND estado = 'activa'
      AND (
          tipo_acceso = 'curso_completo'
          OR (
              tipo_acceso = 'nivel'
              AND id_nivel = p_id_nivel
          )
      )
    ORDER BY
        CASE WHEN tipo_acceso = 'curso_completo' THEN 0 ELSE 1 END
    LIMIT 1;

    SELECT COUNT(*)
    INTO v_existe
    FROM progreso_nivel
    WHERE id_inscripcion = v_id_inscripcion
      AND id_nivel = p_id_nivel;

    IF v_existe > 0 THEN
        UPDATE progreso_nivel
        SET porcentaje = p_porcentaje,
            completado = IF(p_porcentaje = 100, 1, 0),
            fecha_ultimo_acceso = NOW(),
            fecha_completado = IF(
                p_porcentaje = 100,
                COALESCE(fecha_completado, NOW()),
                NULL
            )
        WHERE id_inscripcion = v_id_inscripcion
          AND id_nivel = p_id_nivel;
    ELSE
        INSERT INTO progreso_nivel (
            id_inscripcion,
            id_nivel,
            porcentaje,
            completado,
            fecha_ultimo_acceso,
            fecha_completado
        )
        VALUES (
            v_id_inscripcion,
            p_id_nivel,
            p_porcentaje,
            IF(p_porcentaje = 100, 1, 0),
            NOW(),
            IF(p_porcentaje = 100, NOW(), NULL)
        );
    END IF;

    SELECT 1 AS exito,
           'Progreso actualizado correctamente.' AS mensaje;
END$$


-- 7. Consultar progreso de un curso

DROP PROCEDURE IF EXISTS SP_ConsultarProgresoCurso;

DELIMITER $$

CREATE PROCEDURE SP_ConsultarProgresoCurso(
    IN p_id_usuario INT,
    IN p_id_curso INT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM inscripciones
        WHERE id_usuario = p_id_usuario
          AND id_curso = p_id_curso
          AND estado = 'activa'
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No tienes una inscripción activa en este curso.';
    END IF;

    SELECT
        n.id_nivel,
        n.titulo,
        n.orden,
        COALESCE(p.porcentaje, 0) AS porcentaje,
        COALESCE(p.completado, 0) AS completado,
        p.fecha_ultimo_acceso,
        p.fecha_completado
    FROM niveles n
    LEFT JOIN progreso_nivel p
        ON p.id_nivel = n.id_nivel
       AND p.id_inscripcion = (
            SELECT i.id_inscripcion
            FROM inscripciones i
            WHERE i.id_usuario = p_id_usuario
              AND i.id_curso = p_id_curso
              AND i.estado = 'activa'
              AND (
                  i.tipo_acceso = 'curso_completo'
                  OR (
                      i.tipo_acceso = 'nivel'
                      AND i.id_nivel = n.id_nivel
                  )
              )
            ORDER BY
                CASE WHEN i.tipo_acceso = 'curso_completo' THEN 0 ELSE 1 END
            LIMIT 1
       )
    WHERE n.id_curso = p_id_curso
      AND n.activo = 1
      AND (
          EXISTS (
              SELECT 1
              FROM inscripciones i
              WHERE i.id_usuario = p_id_usuario
                AND i.id_curso = p_id_curso
                AND i.tipo_acceso = 'curso_completo'
                AND i.estado = 'activa'
          )
          OR EXISTS (
              SELECT 1
              FROM inscripciones i
              WHERE i.id_usuario = p_id_usuario
                AND i.id_curso = p_id_curso
                AND i.tipo_acceso = 'nivel'
                AND i.id_nivel = n.id_nivel
                AND i.estado = 'activa'
          )
      )
    ORDER BY n.orden;
END$$

DELIMITER ;
