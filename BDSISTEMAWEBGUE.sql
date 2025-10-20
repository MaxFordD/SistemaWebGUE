-- =============================================
-- BASE DE DATOS: BDSistemaWebGUE
-- Sistema Web para Institución Educativa
-- =============================================

CREATE DATABASE BDSistemaWebGUE;
GO
USE BDSistemaWebGUE;
GO

-- =============================================
-- CATÁLOGOS
-- =============================================

-- Tabla: Rol
CREATE TABLE Rol (
  rol_id      INT IDENTITY(1,1) CONSTRAINT PK_Rol PRIMARY KEY,
  nombre      VARCHAR(50)  NOT NULL CONSTRAINT UQ_Rol_Nombre UNIQUE,
  descripcion VARCHAR(200) NULL,
  estado      CHAR(1)      NOT NULL
  CONSTRAINT DF_Rol_Estado DEFAULT ('A')
  CONSTRAINT CK_Rol_Estado CHECK (estado IN ('A','I'))
);
GO

-- Tabla: Persona
CREATE TABLE Persona (
  persona_id INT IDENTITY(1,1) CONSTRAINT PK_Persona PRIMARY KEY,
  nombres    NVARCHAR(100) NOT NULL,
  apellidos  NVARCHAR(100) NOT NULL,
  dni        CHAR(8)       NULL CONSTRAINT UQ_Persona_Dni UNIQUE,
  telefono   CHAR(9)       NULL,
  correo     VARCHAR(100)  NULL CONSTRAINT UQ_Persona_Correo UNIQUE,
  estado     CHAR(1)       NOT NULL
  CONSTRAINT DF_Persona_Estado DEFAULT ('A')
  CONSTRAINT CK_Persona_Estado CHECK (estado IN ('A','I')),
  CONSTRAINT CK_Persona_Dni_Fmt CHECK (dni IS NULL OR dni LIKE '[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]'),
  CONSTRAINT CK_Persona_Tel_Fmt CHECK (telefono IS NULL OR telefono LIKE '[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]')
);
GO

-- =============================================
-- SEGURIDAD / USUARIOS
-- =============================================

-- Tabla: Usuario
CREATE TABLE Usuario (
  usuario_id     INT IDENTITY(1,1) CONSTRAINT PK_Usuario PRIMARY KEY,
  persona_id     INT NOT NULL,
  nombre_usuario VARCHAR(100) NOT NULL CONSTRAINT UQ_Usuario_Nombre UNIQUE,
  contrasena     NVARCHAR(200) NOT NULL, -- hash/derivado, no texto plano
  estado         CHAR(1) NOT NULL
  CONSTRAINT DF_Usuario_Estado DEFAULT ('A')
  CONSTRAINT CK_Usuario_Estado CHECK (estado IN ('A','I')),
  CONSTRAINT FK_Usuario_Persona FOREIGN KEY (persona_id) REFERENCES Persona(persona_id)
);
GO

-- Tabla: UsuarioRol (Relación muchos a muchos)
CREATE TABLE UsuarioRol (
  usuario_id INT NOT NULL,
  rol_id     INT NOT NULL,
  CONSTRAINT PK_UsuarioRol PRIMARY KEY (usuario_id, rol_id),
  CONSTRAINT FK_UsuarioRol_Usuario FOREIGN KEY (usuario_id) REFERENCES Usuario(usuario_id) ON DELETE CASCADE,
  CONSTRAINT FK_UsuarioRol_Rol FOREIGN KEY (rol_id) REFERENCES Rol(rol_id) ON DELETE CASCADE
);
GO

-- =============================================
-- CONTENIDOS
-- =============================================

-- Tabla: Noticia
CREATE TABLE Noticia (
  noticia_id        INT IDENTITY(1,1) CONSTRAINT PK_Noticia PRIMARY KEY,
  titulo            NVARCHAR(200) NOT NULL,
  contenido         NVARCHAR(MAX) NOT NULL,
  imagen            VARCHAR(255)  NULL, -- ruta archivo
  usuario_id        INT NOT NULL,
  fecha_publicacion DATETIME2     NOT NULL
                     CONSTRAINT DF_Noticia_Fecha DEFAULT (SYSUTCDATETIME()),
  estado            CHAR(1)       NOT NULL
                     CONSTRAINT DF_Noticia_Estado DEFAULT ('A')
                     CONSTRAINT CK_Noticia_Estado CHECK (estado IN ('A','I')),
  CONSTRAINT FK_Noticia_Usuario FOREIGN KEY (usuario_id)
    REFERENCES Usuario(usuario_id)
);
GO

-- Tabla: Tipos_Documento
CREATE TABLE Tipos_Documento (
    tipo_id INT IDENTITY(1,1) CONSTRAINT PK_TiposDocumento PRIMARY KEY,
    nombre NVARCHAR(50) NOT NULL UNIQUE
);
GO

-- =============================================
-- MESA DE PARTES
-- =============================================

-- Tabla: Mesa_Partes
CREATE TABLE Mesa_Partes (
    documento_id INT IDENTITY(1,1) CONSTRAINT PK_MesaPartes PRIMARY KEY,
    remitente    NVARCHAR(150) NOT NULL,
    dni          CHAR(8)       NULL,
    correo       VARCHAR(100)  NULL,
    asunto       NVARCHAR(200) NOT NULL,
    detalle      NVARCHAR(MAX) NULL,
    archivo      VARCHAR(255)  NULL,
    tipo_documento_id INT NOT NULL
        CONSTRAINT DF_MP_TipoDocId DEFAULT (4) -- 'Otro' por defecto
        CONSTRAINT FK_MesaPartes_TipoDocumento
            FOREIGN KEY REFERENCES Tipos_Documento(tipo_id),
    fecha_envio  DATETIME2     NOT NULL CONSTRAINT DF_MP_Fecha DEFAULT (SYSUTCDATETIME()),
    estado       NVARCHAR(50)  NOT NULL CONSTRAINT DF_MP_Estado DEFAULT (N'Pendiente'),
    CONSTRAINT CK_MP_Dni_Fmt CHECK (dni IS NULL OR dni LIKE '[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]'),
    CONSTRAINT CK_MP_Estado CHECK (estado IN (N'Pendiente', N'Revisado'))
);
GO

-- =============================================
-- COMITÉ DIRECTIVO
-- =============================================

-- Tabla: Comite_Directivo
CREATE TABLE Comite_Directivo (
    directivo_id    INT IDENTITY(1,1) CONSTRAINT PK_ComiteDirectivo PRIMARY KEY,
    nombre_completo NVARCHAR(200) NOT NULL,
    cargo           NVARCHAR(100) NOT NULL,
    grado_cargo     NVARCHAR(100) NULL, -- Ej: "1° y 2° grado", "Todos los grados"
    foto            VARCHAR(500)  NULL, -- ruta de archivo
    biografia       NVARCHAR(MAX) NULL,
    orden           INT           NOT NULL CONSTRAINT DF_CD_Orden DEFAULT(0), -- Para ordenar visualmente
    estado          CHAR(1)       NOT NULL
                    CONSTRAINT DF_CD_Estado DEFAULT ('A')
                    CONSTRAINT CK_CD_Estado CHECK (estado IN ('A','I'))
);
GO

-- =============================================
-- AUDITORÍA / BITÁCORA
-- =============================================

-- Tabla: Bitacora
CREATE TABLE Bitacora (
  bitacora_id INT IDENTITY(1,1) CONSTRAINT PK_Bitacora PRIMARY KEY,
  usuario_id  INT NOT NULL,
  accion      NVARCHAR(200) NOT NULL,
  fecha       DATETIME2     NOT NULL
              CONSTRAINT DF_Bitacora_Fecha DEFAULT (SYSUTCDATETIME()),
  CONSTRAINT FK_Bitacora_Usuario FOREIGN KEY (usuario_id)
    REFERENCES Usuario(usuario_id)
);
GO

-- =============================================
-- MENSAJERÍA INTERNA
-- =============================================

-- Tabla: Mensaje
CREATE TABLE Mensaje (
  mensaje_id              INT IDENTITY(1,1) CONSTRAINT PK_Mensaje PRIMARY KEY,
  remitente_usuario_id    INT NOT NULL,
  destinatario_usuario_id INT NOT NULL,
  asunto                  NVARCHAR(200) NULL,
  cuerpo                  NVARCHAR(MAX) NOT NULL,
  creado_en               DATETIME2 NOT NULL
                          CONSTRAINT DF_Mensaje_Creado DEFAULT (SYSUTCDATETIME()),
  leido_en                DATETIME2 NULL,
  CONSTRAINT FK_Msj_Remitente FOREIGN KEY (remitente_usuario_id)
    REFERENCES Usuario(usuario_id),
  CONSTRAINT FK_Msj_Destinatario FOREIGN KEY (destinatario_usuario_id)
    REFERENCES Usuario(usuario_id)
);
GO

-- =============================================
-- DATOS INICIALES
-- =============================================

-- Insertar tipos de documento
INSERT INTO Tipos_Documento (nombre) VALUES
    (N'Solicitud'),
    (N'Reclamo'),
    (N'Sugerencia'),
    (N'Otro');
GO

-- Insertar directivos de ejemplo
INSERT INTO Comite_Directivo (nombre_completo, cargo, grado_cargo, orden, estado) VALUES
    (N'Dr. Juan Pérez Rodríguez', N'Director General', N'Todos los grados', 1, 'A'),
    (N'Mgtr. María González Silva', N'Subdirectora de Formación General', N'1° y 2° grado', 2, 'A'),
    (N'Pendiente de designación', N'Subdirector de 3° grado', N'3° grado', 3, 'A'),
    (N'Lic. Carlos Mendoza Torres', N'Coordinador Académico', N'4° y 5° grado', 4, 'A'),
    (N'Lic. Ana Flores Castillo', N'Coordinadora de Tutoría', N'Todos los grados', 5, 'A');
GO

PRINT '=============================================';
PRINT 'Base de Datos BDSistemaWebGUE creada exitosamente';
PRINT 'Tablas: 10';
PRINT 'Tipos de Documento: 4 registros';
PRINT 'Comité Directivo: 5 registros de ejemplo';
PRINT '=============================================';
