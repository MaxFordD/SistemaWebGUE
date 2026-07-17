-- Base de Datos: BDSistemaWebGUE (MySQL)

SET FOREIGN_KEY_CHECKS = 0;

-- =============================================
-- ELIMINAR TABLAS EXISTENTES
-- =============================================
DROP TABLE IF EXISTS Asistencia;
DROP TABLE IF EXISTS Alumno;
DROP TABLE IF EXISTS Seccion;
DROP TABLE IF EXISTS Grado;
DROP TABLE IF EXISTS imagenes_inicio;
DROP TABLE IF EXISTS Historia_Legado;
DROP TABLE IF EXISTS Bitacora;
DROP TABLE IF EXISTS Mensaje;
DROP TABLE IF EXISTS Mesa_Partes;
DROP TABLE IF EXISTS Tipos_Documento;
DROP TABLE IF EXISTS Noticia;
DROP TABLE IF EXISTS UsuarioRol;
DROP TABLE IF EXISTS Usuario;
DROP TABLE IF EXISTS Persona;
DROP TABLE IF EXISTS Comite_Directivo;
DROP TABLE IF EXISTS Rol;

-- Tabla: Rol
CREATE TABLE Rol (
  rol_id      INT AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(50)  NOT NULL UNIQUE,
  descripcion VARCHAR(200) NULL,
  estado      CHAR(1)      NOT NULL DEFAULT 'A'
);

-- Tabla: Persona
CREATE TABLE Persona (
  persona_id INT AUTO_INCREMENT PRIMARY KEY,
  nombres    VARCHAR(100) NOT NULL,
  apellidos  VARCHAR(100) NOT NULL,
  dni        CHAR(8)      NULL UNIQUE,
  telefono   CHAR(9)      NULL,
  correo     VARCHAR(100) NULL UNIQUE,
  estado     CHAR(1)      NOT NULL DEFAULT 'A'
);

-- Tabla: Usuario
CREATE TABLE Usuario (
  usuario_id     INT AUTO_INCREMENT PRIMARY KEY,
  persona_id     INT NOT NULL,
  nombre_usuario VARCHAR(100)  NOT NULL UNIQUE,
  contrasena     VARCHAR(200)  NOT NULL,
  estado         CHAR(1)       NOT NULL DEFAULT 'A',
  CONSTRAINT FK_Usuario_Persona FOREIGN KEY (persona_id) REFERENCES Persona(persona_id)
);

-- Tabla: UsuarioRol
CREATE TABLE UsuarioRol (
  usuario_id INT NOT NULL,
  rol_id     INT NOT NULL,
  PRIMARY KEY (usuario_id, rol_id),
  CONSTRAINT FK_UsuarioRol_Usuario FOREIGN KEY (usuario_id) REFERENCES Usuario(usuario_id) ON DELETE CASCADE,
  CONSTRAINT FK_UsuarioRol_Rol    FOREIGN KEY (rol_id)     REFERENCES Rol(rol_id)     ON DELETE CASCADE
);

-- Tabla: Noticia
CREATE TABLE Noticia (
  noticia_id        INT AUTO_INCREMENT PRIMARY KEY,
  titulo            VARCHAR(200) NOT NULL,
  contenido         TEXT         NOT NULL,
  imagen            VARCHAR(255) NULL,
  usuario_id        INT          NOT NULL,
  fecha_publicacion DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  estado            CHAR(1)      NOT NULL DEFAULT 'A',
  CONSTRAINT FK_Noticia_Usuario FOREIGN KEY (usuario_id) REFERENCES Usuario(usuario_id)
);

-- Tabla: Tipos_Documento
CREATE TABLE Tipos_Documento (
  tipo_id INT AUTO_INCREMENT PRIMARY KEY,
  nombre  VARCHAR(50) NOT NULL UNIQUE
);

-- Tabla: Mesa_Partes
CREATE TABLE Mesa_Partes (
  documento_id      INT AUTO_INCREMENT PRIMARY KEY,
  remitente         VARCHAR(150) NOT NULL,
  dni               CHAR(8)      NULL,
  correo            VARCHAR(100) NULL,
  asunto            VARCHAR(200) NOT NULL,
  detalle           TEXT         NULL,
  archivo           VARCHAR(255) NULL,
  tipo_documento_id INT          NOT NULL DEFAULT 4,
  fecha_envio       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  estado            VARCHAR(50)  NOT NULL DEFAULT 'Pendiente',
  CONSTRAINT FK_MesaPartes_TipoDocumento FOREIGN KEY (tipo_documento_id) REFERENCES Tipos_Documento(tipo_id)
);

-- Tabla: Comite_Directivo
CREATE TABLE Comite_Directivo (
  directivo_id    INT AUTO_INCREMENT PRIMARY KEY,
  nombre_completo VARCHAR(200) NOT NULL,
  cargo           VARCHAR(100) NOT NULL,
  grado_cargo     VARCHAR(100) NULL,
  foto            VARCHAR(500) NULL,
  biografia       TEXT         NULL,
  orden           INT          NOT NULL DEFAULT 0,
  estado          CHAR(1)      NOT NULL DEFAULT 'A'
);

-- Tabla: Historia_Legado
CREATE TABLE Historia_Legado (
  item_id    INT AUTO_INCREMENT PRIMARY KEY,
  tipo       ENUM('foto','texto','video') NOT NULL,
  titulo     VARCHAR(200) NOT NULL,
  contenido  TEXT NULL,
  archivo    VARCHAR(500) NULL,
  url_video  VARCHAR(500) NULL,
  orden      SMALLINT NOT NULL DEFAULT 0,
  estado     CHAR(1) NOT NULL DEFAULT 'A',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: Bitacora
CREATE TABLE Bitacora (
  bitacora_id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT          NOT NULL,
  accion      VARCHAR(200) NOT NULL,
  fecha       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT FK_Bitacora_Usuario FOREIGN KEY (usuario_id) REFERENCES Usuario(usuario_id)
);

-- Tabla: Mensaje
CREATE TABLE Mensaje (
  mensaje_id              INT AUTO_INCREMENT PRIMARY KEY,
  remitente_usuario_id    INT  NOT NULL,
  destinatario_usuario_id INT  NOT NULL,
  asunto                  VARCHAR(200) NULL,
  cuerpo                  TEXT         NOT NULL,
  creado_en               DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  leido_en                DATETIME     NULL,
  CONSTRAINT FK_Msj_Remitente    FOREIGN KEY (remitente_usuario_id)    REFERENCES Usuario(usuario_id),
  CONSTRAINT FK_Msj_Destinatario FOREIGN KEY (destinatario_usuario_id) REFERENCES Usuario(usuario_id)
);

-- =============================================
-- MÓDULO DE ASISTENCIA ESCOLAR
-- =============================================

-- Tabla: Grado
CREATE TABLE Grado (
  grado_id INT AUTO_INCREMENT PRIMARY KEY,
  nombre   VARCHAR(30) NOT NULL,
  nivel    ENUM('Primaria','Secundaria') NOT NULL,
  estado   TINYINT NOT NULL DEFAULT 1
);

-- Tabla: Seccion
CREATE TABLE Seccion (
  seccion_id  INT AUTO_INCREMENT PRIMARY KEY,
  grado_id    INT NOT NULL,
  nombre      VARCHAR(10) NOT NULL,
  turno       ENUM('Mañana','Tarde') NOT NULL DEFAULT 'Mañana',
  año_lectivo SMALLINT NOT NULL,
  estado      TINYINT NOT NULL DEFAULT 1,
  CONSTRAINT FK_Seccion_Grado FOREIGN KEY (grado_id) REFERENCES Grado(grado_id)
);

-- Tabla: Alumno
CREATE TABLE Alumno (
  alumno_id        INT AUTO_INCREMENT PRIMARY KEY,
  seccion_id       INT NOT NULL,
  nombres          VARCHAR(100) NOT NULL,
  apellidos        VARCHAR(100) NOT NULL,
  dni              VARCHAR(8)   NOT NULL UNIQUE,
  fecha_nacimiento DATE         NULL,
  sexo             ENUM('M','F') NOT NULL,
  estado           TINYINT NOT NULL DEFAULT 1,
  CONSTRAINT FK_Alumno_Seccion FOREIGN KEY (seccion_id) REFERENCES Seccion(seccion_id)
);

-- Tabla: Asistencia
CREATE TABLE Asistencia (
  asistencia_id     INT AUTO_INCREMENT PRIMARY KEY,
  alumno_id         INT NOT NULL,
  usuario_id        INT NOT NULL,
  fecha             DATE NOT NULL,
  estado_asistencia ENUM('Asistio','Falta','Tardanza') NOT NULL,
  observacion       TEXT NULL,
  created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT UQ_Asistencia_Alumno_Fecha UNIQUE (alumno_id, fecha),
  CONSTRAINT FK_Asistencia_Alumno  FOREIGN KEY (alumno_id)  REFERENCES Alumno(alumno_id),
  CONSTRAINT FK_Asistencia_Usuario FOREIGN KEY (usuario_id) REFERENCES Usuario(usuario_id)
);

-- Tabla: imagenes_inicio
CREATE TABLE imagenes_inicio (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seccion     ENUM('carousel','taller') NOT NULL,
  orden       TINYINT UNSIGNED NOT NULL DEFAULT 1,
  ruta        VARCHAR(255) NOT NULL,
  alt         VARCHAR(255) NOT NULL DEFAULT '',
  titulo      VARCHAR(100) NULL,
  descripcion VARCHAR(255) NULL,
  icono       VARCHAR(50)  NULL,
  activo      TINYINT NOT NULL DEFAULT 1,
  created_at  TIMESTAMP NULL,
  updated_at  TIMESTAMP NULL
);

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- DATOS INICIALES
-- =============================================

INSERT INTO Tipos_Documento (nombre) VALUES
  ('Solicitud'),
  ('Reclamo'),
  ('Sugerencia'),
  ('Otro');

INSERT INTO Comite_Directivo (nombre_completo, cargo, grado_cargo, orden, estado) VALUES
  ('Dr. Juan Pérez Rodríguez',        'Director General',                 'Todos los grados', 1, 'A'),
  ('Mgtr. María González Silva',      'Subdirectora de Formación General','1° y 2° grado',    2, 'A'),
  ('Pendiente de designación',        'Subdirector de 3° grado',          '3° grado',         3, 'A'),
  ('Lic. Carlos Mendoza Torres',      'Coordinador Académico',            '4° y 5° grado',    4, 'A'),
  ('Lic. Ana Flores Castillo',        'Coordinadora de Tutoría',          'Todos los grados', 5, 'A');

INSERT IGNORE INTO Rol (nombre, descripcion) VALUES ('Auxiliar', 'Gestión de asistencia de alumnos');

INSERT INTO imagenes_inicio (seccion, orden, ruta, alt, titulo, descripcion, icono, activo, created_at, updated_at) VALUES
  ('carousel', 1, 'images/gue.jpg',              'Fachada del colegio',        NULL,              NULL,                                                         NULL,                1, NOW(), NOW()),
  ('carousel', 2, 'images/colegio001.jpg',       'Estudiantes en actividades', NULL,              NULL,                                                         NULL,                1, NOW(), NOW()),
  ('carousel', 3, 'images/colegio003.jpg',       'Instalaciones del campus',   NULL,              NULL,                                                         NULL,                1, NOW(), NOW()),
  ('taller',   1, 'images/talleres/musica.jpg',  'Taller de Música',           'Música',          'Práctica instrumental, ensambles y teoría musical.',        'music-note-beamed', 1, NOW(), NOW()),
  ('taller',   2, 'images/talleres/deporte.jpg', 'Taller de Deporte',          'Deporte',         'Fútbol, vóley y atletismo para todas las categorías.',      'trophy',            1, NOW(), NOW()),
  ('taller',   3, 'images/talleres/pintura.jpg', 'Taller de Artes Plásticas',  'Artes Plásticas', 'Dibujo, pintura y técnicas mixtas.',                        'palette',           1, NOW(), NOW()),
  ('taller',   4, 'images/talleres/danza.jpg',   'Taller de Danza',            'Danza',           'Danza moderna y folclore peruano.',                         'person-arms-up',    1, NOW(), NOW());
