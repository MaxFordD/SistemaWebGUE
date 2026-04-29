-- Base de Datos: BDSistemaWebGUE (MySQL)
-- Convertido desde SQL Server

SET FOREIGN_KEY_CHECKS = 0;

-- Tabla: Rol
CREATE TABLE Rol (
  rol_id      INT AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(50)  NOT NULL UNIQUE,
  descripcion VARCHAR(200) NULL,
  estado      CHAR(1)      NOT NULL DEFAULT 'A',
  CONSTRAINT CK_Rol_Estado CHECK (estado IN ('A','I'))
);

-- Tabla: Persona
CREATE TABLE Persona (
  persona_id INT AUTO_INCREMENT PRIMARY KEY,
  nombres    VARCHAR(100) NOT NULL,
  apellidos  VARCHAR(100) NOT NULL,
  dni        CHAR(8)      NULL UNIQUE,
  telefono   CHAR(9)      NULL,
  correo     VARCHAR(100) NULL UNIQUE,
  estado     CHAR(1)      NOT NULL DEFAULT 'A',
  CONSTRAINT CK_Persona_Estado CHECK (estado IN ('A','I'))
);

-- Tabla: Usuario
CREATE TABLE Usuario (
  usuario_id     INT AUTO_INCREMENT PRIMARY KEY,
  persona_id     INT NOT NULL,
  nombre_usuario VARCHAR(100)  NOT NULL UNIQUE,
  contrasena     VARCHAR(200)  NOT NULL,
  estado         CHAR(1)       NOT NULL DEFAULT 'A',
  CONSTRAINT CK_Usuario_Estado CHECK (estado IN ('A','I')),
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
  CONSTRAINT CK_Noticia_Estado CHECK (estado IN ('A','I')),
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
  CONSTRAINT CK_MP_Estado CHECK (estado IN ('Pendiente','Revisado')),
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
  estado          CHAR(1)      NOT NULL DEFAULT 'A',
  CONSTRAINT CK_CD_Estado CHECK (estado IN ('A','I'))
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
