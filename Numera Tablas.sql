CREATE DATABASE Numera;

USE Numera;

CREATE TABLE Usuarios (
	ID INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(90),
    genero ENUM('femenino', 'masculino', 'otro', 'prefiero no especificarlo'),
    fecha_nacimiento DATE,
	foto_perfil VARCHAR(255),
    correo VARCHAR(50),
    contrasena VARCHAR(15),
    fecha_ingreso TIMESTAMP,
    rol ENUM('estudiante', 'instructor', 'administrador')
);

INSERT INTO Usuarios(nombre_completo, genero, fecha_nacimiento, correo, contrasena, rol)
VALUES('Ricardo Govea Godínez', 'masculino', '2001-11-29', 'goveagricky@gmail.com', 'hw.#697406', 'administrador');

SELECT * FROM Usuarios;

CREATE TABLE Cursos (
	ID INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50),
    instructor_ID INT, 
    FOREIGN KEY(instructor_ID) REFERENCES Usuarios(ID),
    alumno_ID INT, 
    FOREIGN KEY(alumno_ID) REFERENCES Usuarios(ID),
    estatus TINYINT
);

CREATE TABLE Diplomas (
	ID INT AUTO_INCREMENT PRIMARY KEY,
    curso_ID INT, 
    FOREIGN KEY(curso_ID) REFERENCES Cursos(ID),
    alumno_ID INT, 
    FOREIGN KEY(alumno_ID) REFERENCES Usuarios(ID),
    instructor_ID INT, 
    FOREIGN KEY(instructor_ID) REFERENCES Usuarios(ID),
    fecha_completado DATE
);

CREATE TABLE Categorias (
	ID INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50),
    instructor_ID INT, 
    FOREIGN KEY(instructor_ID) REFERENCES Usuarios(ID),
    fecha TIMESTAMP
);

CREATE TABLE Mensajes (
	ID INT AUTO_INCREMENT PRIMARY KEY,
	remitente INT, 
    FOREIGN KEY(remitente) REFERENCES Usuarios(ID),
    destinatario INT, 
    FOREIGN KEY(destinatario) REFERENCES Usuarios(ID),
    texto TEXT,
    fecha TIMESTAMP
);

CREATE TABLE Comentarios (
	ID INT AUTO_INCREMENT PRIMARY KEY,
	curso_ID INT,
	FOREIGN KEY(curso_ID) REFERENCES Cursos(ID),
    usuario_ID INT,
	FOREIGN KEY(usuario_ID) REFERENCES Usuarios(ID),
    calificacion DECIMAL,
    fecha TIMESTAMP
);

CREATE TABLE Kardex (
	ID INT AUTO_INCREMENT PRIMARY KEY,
	alumno_ID INT, 
    FOREIGN KEY(alumno_ID) REFERENCES Usuarios(ID_U)
);

CREATE TABLE Ventas (
	ID INT AUTO_INCREMENT PRIMARY KEY,
	instructor_ID INT, 
    FOREIGN KEY(instructor_ID) REFERENCES Usuarios(ID_U),
    cantidad DECIMAL(10,2)
);
