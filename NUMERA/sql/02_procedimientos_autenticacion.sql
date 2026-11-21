
USE numera;

DROP PROCEDURE IF EXISTS SP_RegistrarUsuario;
DROP PROCEDURE IF EXISTS SP_ObtenerUsuarioLogin;
DROP PROCEDURE IF EXISTS SP_ActualizarIntentosFallidos;
DROP PROCEDURE IF EXISTS SP_ReiniciarIntentosLogin;
DROP PROCEDURE IF EXISTS SP_ConsultarUsuarioSesion;

DELIMITER $$

-- REGISTRAR USUARIO
CREATE PROCEDURE SP_RegistrarUsuario(
    IN p_nombre_completo VARCHAR(120),
    IN p_nombre_usuario VARCHAR(40),
    IN p_correo VARCHAR(120),
    IN p_contrasena_hash VARCHAR(255),
    IN p_genero VARCHAR(30),
    IN p_fecha_nacimiento DATE,
    IN p_rol VARCHAR(20)
)
BEGIN
    IF p_rol NOT IN ('estudiante', 'instructor') THEN

        SELECT 0 AS exito, 'rol' AS campo,
        'El rol solicitado no está permitido.' AS mensaje;

    ELSEIF EXISTS (
        SELECT 1 FROM usuarios
        WHERE nombre_usuario = p_nombre_usuario
    ) THEN

        SELECT 0 AS exito, 'nombre_usuario' AS campo,
        'El nombre de usuario ya está registrado.' AS mensaje;

    ELSEIF EXISTS (
        SELECT 1 FROM usuarios
        WHERE correo = p_correo
    ) THEN

        SELECT 0 AS exito, 'correo' AS campo,
        'El correo electrónico ya está registrado.' AS mensaje;

    ELSE

        INSERT INTO usuarios (
            nombre_completo,
            nombre_usuario,
            correo,
            contrasena,
            genero,
            fecha_nacimiento,
            rol
        )
        VALUES (
            p_nombre_completo,
            p_nombre_usuario,
            p_correo,
            p_contrasena_hash,
            COALESCE(
                NULLIF(p_genero, ''),
                'prefiero_no_especificarlo'
            ),
            p_fecha_nacimiento,
            p_rol
        );

        SELECT
            1 AS exito,
            LAST_INSERT_ID() AS id_usuario,
            'Usuario registrado correctamente.' AS mensaje;

    END IF;
END$$


-- CONSULTAR USUARIO PARA INICIAR SESIÓN
CREATE PROCEDURE SP_ObtenerUsuarioLogin(
    IN p_identificador VARCHAR(120)
)
BEGIN
    SELECT
        id_usuario,
        nombre_completo,
        nombre_usuario,
        correo,
        contrasena,
        rol,
        activo,
        bloqueado,
        intentos_fallidos
    FROM usuarios
    WHERE correo = p_identificador
       OR nombre_usuario = p_identificador
    LIMIT 1;
END$$


-- AUMENTAR INTENTOS FALLIDOS Y BLOQUEAR AL TERCERO
CREATE PROCEDURE SP_ActualizarIntentosFallidos(
    IN p_id_usuario INT
)
BEGIN
    UPDATE usuarios
    SET
        bloqueado = IF(intentos_fallidos >= 2, 1, bloqueado),
        intentos_fallidos = LEAST(intentos_fallidos + 1, 3)
    WHERE id_usuario = p_id_usuario;

    SELECT
        intentos_fallidos,
        bloqueado
    FROM usuarios
    WHERE id_usuario = p_id_usuario;
END$$


-- REINICIAR INTENTOS DESPUÉS DE UN LOGIN CORRECTO
CREATE PROCEDURE SP_ReiniciarIntentosLogin(
    IN p_id_usuario INT
)
BEGIN
    UPDATE usuarios
    SET intentos_fallidos = 0
    WHERE id_usuario = p_id_usuario;
END$$


-- CONSULTAR INFORMACIÓN PÚBLICA DE LA SESIÓN
CREATE PROCEDURE SP_ConsultarUsuarioSesion(
    IN p_id_usuario INT
)
BEGIN
    SELECT
        id_usuario,
        nombre_completo,
        nombre_usuario,
        correo,
        genero,
        fecha_nacimiento,
        foto_perfil,
        rol,
        activo,
        bloqueado,
        fecha_registro
    FROM usuarios
    WHERE id_usuario = p_id_usuario
    LIMIT 1;
END$$

DELIMITER ;
