-- NUMERA | Esquema inicial de base de datos
-- Compatible con MySQL/MariaDB en XAMPP.
-- Ejecutar en phpMyAdmin (pestaña SQL). Este script RECREA la base de datos.
-- IMPORTANTE: DROP DATABASE elimina cualquier base anterior llamada Numera.

DROP DATABASE IF EXISTS Numera;
CREATE DATABASE Numera CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE Numera;

CREATE TABLE Usuarios (
 id_usuario INT AUTO_INCREMENT PRIMARY KEY,
 nombre_completo VARCHAR(120) NOT NULL,
 nombre_usuario VARCHAR(40) NOT NULL UNIQUE,
 correo VARCHAR(120) NOT NULL UNIQUE,
 contrasena VARCHAR(255) NOT NULL COMMENT 'Hash generado con password_hash() de PHP',
 genero ENUM('femenino','masculino','otro','prefiero_no_especificarlo') DEFAULT 'prefiero_no_especificarlo',
 fecha_nacimiento DATE NULL,
 foto_perfil VARCHAR(255) NULL,
 rol ENUM('estudiante','instructor','administrador') NOT NULL DEFAULT 'estudiante',
 activo TINYINT(1) NOT NULL DEFAULT 1,
 intentos_fallidos TINYINT UNSIGNED NOT NULL DEFAULT 0,
 bloqueado TINYINT(1) NOT NULL DEFAULT 0,
 fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE Categorias (
 id_categoria INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(80) NOT NULL UNIQUE,
 descripcion VARCHAR(255) NULL,
 activa TINYINT(1) NOT NULL DEFAULT 1,
 fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE Cursos (
 id_curso INT AUTO_INCREMENT PRIMARY KEY,
 id_instructor INT NOT NULL,
 titulo VARCHAR(150) NOT NULL,
 descripcion TEXT NOT NULL,
 imagen VARCHAR(255) NULL,
 precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 precio_por_nivel TINYINT(1) NOT NULL DEFAULT 0,
 publicado TINYINT(1) NOT NULL DEFAULT 0,
 eliminado TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Eliminación lógica',
 fecha_publicacion DATETIME NULL,
 fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_cursos_instructor FOREIGN KEY (id_instructor) REFERENCES Usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
 INDEX idx_cursos_publicados (publicado, eliminado, fecha_publicacion),
 INDEX idx_cursos_titulo (titulo)
) ENGINE=InnoDB;

CREATE TABLE Curso_Categoria (
 id_curso INT NOT NULL,
 id_categoria INT NOT NULL,
 PRIMARY KEY (id_curso, id_categoria),
 CONSTRAINT fk_cc_curso FOREIGN KEY (id_curso) REFERENCES Cursos(id_curso) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT fk_cc_categoria FOREIGN KEY (id_categoria) REFERENCES Categorias(id_categoria) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Niveles (
 id_nivel INT AUTO_INCREMENT PRIMARY KEY,
 id_curso INT NOT NULL,
 titulo VARCHAR(150) NOT NULL,
 descripcion TEXT NULL,
 orden INT NOT NULL,
 precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 activo TINYINT(1) NOT NULL DEFAULT 1,
 CONSTRAINT fk_niveles_curso FOREIGN KEY (id_curso) REFERENCES Cursos(id_curso) ON DELETE CASCADE ON UPDATE CASCADE,
 UNIQUE KEY uq_nivel_orden (id_curso, orden)
) ENGINE=InnoDB;

CREATE TABLE Contenido_Nivel (
 id_contenido INT AUTO_INCREMENT PRIMARY KEY,
 id_nivel INT NOT NULL,
 tipo ENUM('video','texto','pdf','imagen','enlace') NOT NULL,
 titulo VARCHAR(150) NOT NULL,
 descripcion TEXT NULL,
 ruta_archivo VARCHAR(255) NULL,
 url_externa VARCHAR(500) NULL,
 texto_contenido LONGTEXT NULL,
 orden INT NOT NULL DEFAULT 1,
 fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_contenido_nivel FOREIGN KEY (id_nivel) REFERENCES Niveles(id_nivel) ON DELETE CASCADE ON UPDATE CASCADE,
 INDEX idx_contenido_nivel_orden (id_nivel, orden)
) ENGINE=InnoDB;

CREATE TABLE Inscripciones (
 id_inscripcion INT AUTO_INCREMENT PRIMARY KEY,
 id_usuario INT NOT NULL,
 id_curso INT NOT NULL,
 tipo_acceso ENUM('curso_completo','nivel') NOT NULL DEFAULT 'curso_completo',
 id_nivel INT NULL,
 fecha_inscripcion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 estado ENUM('activa','completada','cancelada') NOT NULL DEFAULT 'activa',
 ultimo_acceso DATETIME NULL,
 CONSTRAINT fk_ins_usuario FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
 CONSTRAINT fk_ins_curso FOREIGN KEY (id_curso) REFERENCES Cursos(id_curso) ON DELETE RESTRICT ON UPDATE CASCADE,
 CONSTRAINT fk_ins_nivel FOREIGN KEY (id_nivel) REFERENCES Niveles(id_nivel) ON DELETE RESTRICT ON UPDATE CASCADE,
 INDEX idx_ins_usuario (id_usuario, fecha_inscripcion),
 INDEX idx_ins_curso (id_curso)
) ENGINE=InnoDB;

CREATE TABLE Progreso_Nivel (
 id_progreso INT AUTO_INCREMENT PRIMARY KEY,
 id_inscripcion INT NOT NULL,
 id_nivel INT NOT NULL,
 porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0.00,
 completado TINYINT(1) NOT NULL DEFAULT 0,
 fecha_ultimo_acceso DATETIME NULL,
 fecha_completado DATETIME NULL,
 CONSTRAINT fk_progreso_inscripcion FOREIGN KEY (id_inscripcion) REFERENCES Inscripciones(id_inscripcion) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT fk_progreso_nivel FOREIGN KEY (id_nivel) REFERENCES Niveles(id_nivel) ON DELETE RESTRICT ON UPDATE CASCADE,
 UNIQUE KEY uq_progreso_ins_nivel (id_inscripcion, id_nivel)
) ENGINE=InnoDB;

CREATE TABLE Compras (
 id_compra INT AUTO_INCREMENT PRIMARY KEY,
 id_usuario INT NOT NULL,
 id_curso INT NOT NULL,
 id_nivel INT NULL,
 concepto ENUM('curso_completo','nivel') NOT NULL,
 monto DECIMAL(10,2) NOT NULL,
 moneda CHAR(3) NOT NULL DEFAULT 'MXN',
 metodo_pago ENUM('tarjeta','paypal','transferencia','simulado') NOT NULL,
 referencia_pago VARCHAR(150) NULL,
 estado ENUM('pendiente','pagada','rechazada','reembolsada') NOT NULL DEFAULT 'pendiente',
 fecha_compra DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_compra_usuario FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
 CONSTRAINT fk_compra_curso FOREIGN KEY (id_curso) REFERENCES Cursos(id_curso) ON DELETE RESTRICT ON UPDATE CASCADE,
 CONSTRAINT fk_compra_nivel FOREIGN KEY (id_nivel) REFERENCES Niveles(id_nivel) ON DELETE RESTRICT ON UPDATE CASCADE,
 INDEX idx_compra_usuario_fecha (id_usuario, fecha_compra),
 INDEX idx_compra_curso_estado (id_curso, estado)
) ENGINE=InnoDB;

CREATE TABLE Diplomas (
 id_diploma INT AUTO_INCREMENT PRIMARY KEY,
 id_inscripcion INT NOT NULL UNIQUE,
 fecha_completado DATE NOT NULL,
 nombre_estudiante VARCHAR(120) NOT NULL,
 nombre_curso VARCHAR(150) NOT NULL,
 nombre_instructor VARCHAR(120) NOT NULL,
 codigo_certificado VARCHAR(50) NOT NULL UNIQUE,
 ruta_archivo VARCHAR(255) NULL,
 fecha_generacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_diploma_inscripcion FOREIGN KEY (id_inscripcion) REFERENCES Inscripciones(id_inscripcion) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Opiniones (
 id_opinion INT AUTO_INCREMENT PRIMARY KEY,
 id_usuario INT NOT NULL,
 id_curso INT NOT NULL,
 calificacion TINYINT UNSIGNED NOT NULL,
 comentario TEXT NULL,
 visible TINYINT(1) NOT NULL DEFAULT 1,
 fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT chk_opinion_calificacion CHECK (calificacion BETWEEN 1 AND 5),
 CONSTRAINT fk_opinion_usuario FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
 CONSTRAINT fk_opinion_curso FOREIGN KEY (id_curso) REFERENCES Cursos(id_curso) ON DELETE RESTRICT ON UPDATE CASCADE,
 UNIQUE KEY uq_opinion_usuario_curso (id_usuario, id_curso)
) ENGINE=InnoDB;

CREATE TABLE Moderacion_Opiniones (
 id_moderacion INT AUTO_INCREMENT PRIMARY KEY,
 id_opinion INT NOT NULL,
 id_administrador INT NOT NULL,
 motivo VARCHAR(255) NOT NULL,
 comentario_sustituto VARCHAR(255) NOT NULL DEFAULT '[Comentario retirado por moderación]',
 fecha_moderacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_mod_opinion FOREIGN KEY (id_opinion) REFERENCES Opiniones(id_opinion) ON DELETE RESTRICT ON UPDATE CASCADE,
 CONSTRAINT fk_mod_admin FOREIGN KEY (id_administrador) REFERENCES Usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Mensajes (
 id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
 id_remitente INT NOT NULL,
 id_destinatario INT NOT NULL,
 asunto VARCHAR(150) NULL,
 texto TEXT NOT NULL,
 leido TINYINT(1) NOT NULL DEFAULT 0,
 fecha_envio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_msg_remitente FOREIGN KEY (id_remitente) REFERENCES Usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
 CONSTRAINT fk_msg_destinatario FOREIGN KEY (id_destinatario) REFERENCES Usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
 INDEX idx_msg_destinatario_fecha (id_destinatario, fecha_envio),
 INDEX idx_msg_remitente_fecha (id_remitente, fecha_envio)
) ENGINE=InnoDB;

INSERT INTO Categorias (nombre, descripcion) VALUES
('Álgebra', 'Cursos de álgebra y temas relacionados'),
('Cálculo', 'Cursos de cálculo diferencial e integral'),
('Trigonometría', 'Cursos de trigonometría'),
('Física', 'Cursos de física y ciencias relacionadas');

-- La cuenta administradora se creará después desde PHP usando password_hash().
