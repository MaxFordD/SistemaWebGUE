-- Base de Datos: BDSistemaWebGUE (MySQL)

SET FOREIGN_KEY_CHECKS = 0;

-- =============================================
-- ELIMINAR TABLAS EXISTENTES
-- =============================================
DROP TABLE IF EXISTS AsistenciaAuditoria;
DROP TABLE IF EXISTS AsistenciaConfiguracion;
DROP TABLE IF EXISTS DiaNoHabil;
DROP TABLE IF EXISTS Asistencia;
DROP TABLE IF EXISTS Alumno;
DROP TABLE IF EXISTS Seccion;
DROP TABLE IF EXISTS Grado;
DROP TABLE IF EXISTS imagenes_inicio;
DROP TABLE IF EXISTS NosotrosContenido;
DROP TABLE IF EXISTS Historia_Legado;
DROP TABLE IF EXISTS Bitacora;
DROP TABLE IF EXISTS Mensaje;
DROP TABLE IF EXISTS Mesa_Partes;
DROP TABLE IF EXISTS Tipos_Documento;
DROP TABLE IF EXISTS Noticia;
DROP TABLE IF EXISTS RolPermiso;
DROP TABLE IF EXISTS Permiso;
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

-- Tabla: Permiso
CREATE TABLE Permiso (
  permiso_id  INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
  slug        VARCHAR(100) NOT NULL UNIQUE,
  nombre      VARCHAR(150) NOT NULL,
  modulo      VARCHAR(100) NOT NULL,
  descripcion VARCHAR(255) NULL,
  estado      CHAR(1)      NOT NULL DEFAULT 'A'
);

-- Tabla: RolPermiso
CREATE TABLE RolPermiso (
  rol_id     INT NOT NULL,
  permiso_id INT NOT NULL,
  PRIMARY KEY (rol_id, permiso_id),
  CONSTRAINT FK_RolPermiso_Rol     FOREIGN KEY (rol_id)     REFERENCES Rol(rol_id)         ON DELETE CASCADE,
  CONSTRAINT FK_RolPermiso_Permiso FOREIGN KEY (permiso_id) REFERENCES Permiso(permiso_id) ON DELETE CASCADE
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

-- Tabla: NosotrosContenido
CREATE TABLE NosotrosContenido (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  clave      VARCHAR(255) NOT NULL UNIQUE,
  valor      TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
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
  codigo_qr        VARCHAR(40)  NULL UNIQUE,
  fecha_nacimiento DATE         NULL,
  sexo             ENUM('M','F') NOT NULL,
  estado           TINYINT NOT NULL DEFAULT 1,
  CONSTRAINT FK_Alumno_Seccion FOREIGN KEY (seccion_id) REFERENCES Seccion(seccion_id)
);

-- Tabla: Asistencia
CREATE TABLE Asistencia (
  asistencia_id         INT AUTO_INCREMENT PRIMARY KEY,
  alumno_id             INT NOT NULL,
  usuario_id            INT NOT NULL,
  fecha                 DATE NOT NULL,
  estado_asistencia     ENUM('Asistio','Falta','Tardanza','Justificada') NOT NULL,
  hora_registro         TIME NULL,
  observacion           TEXT NULL,
  motivo_justificacion  TEXT NULL,
  created_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT UQ_Asistencia_Alumno_Fecha UNIQUE (alumno_id, fecha),
  CONSTRAINT FK_Asistencia_Alumno  FOREIGN KEY (alumno_id)  REFERENCES Alumno(alumno_id),
  CONSTRAINT FK_Asistencia_Usuario FOREIGN KEY (usuario_id) REFERENCES Usuario(usuario_id)
);

-- Tabla: AsistenciaConfiguracion
CREATE TABLE AsistenciaConfiguracion (
  config_id            TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  hora_apertura        TIME NOT NULL,
  hora_cierre          TIME NOT NULL,
  hora_limite_tardanza TIME NOT NULL,
  umbral_alertas_mes   TINYINT UNSIGNED NOT NULL,
  dias_limite_edicion  TINYINT UNSIGNED NOT NULL,
  actualizado_por      INT NULL,
  actualizado_en       TIMESTAMP NULL,
  CONSTRAINT FK_AsistenciaConfiguracion_Usuario FOREIGN KEY (actualizado_por) REFERENCES Usuario(usuario_id)
);

-- Tabla: AsistenciaAuditoria
CREATE TABLE AsistenciaAuditoria (
  auditoria_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  alumno_id            INT NOT NULL,
  usuario_id           INT NOT NULL,
  fecha_asistencia     DATE NOT NULL,
  estado_anterior      VARCHAR(20) NULL,
  estado_nuevo         VARCHAR(20) NOT NULL,
  observacion_anterior TEXT NULL,
  observacion_nueva    TEXT NULL,
  created_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT FK_AsistenciaAuditoria_Alumno  FOREIGN KEY (alumno_id)  REFERENCES Alumno(alumno_id),
  CONSTRAINT FK_AsistenciaAuditoria_Usuario FOREIGN KEY (usuario_id) REFERENCES Usuario(usuario_id),
  INDEX IDX_AsistenciaAuditoria_AlumnoFecha (alumno_id, fecha_asistencia)
);

-- Tabla: DiaNoHabil
CREATE TABLE DiaNoHabil (
  dia_no_habil_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  fecha           DATE NOT NULL UNIQUE,
  motivo          VARCHAR(150) NOT NULL,
  usuario_id      INT NOT NULL,
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT FK_DiaNoHabil_Usuario FOREIGN KEY (usuario_id) REFERENCES Usuario(usuario_id)
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

-- Permisos del sistema (módulos existentes + módulo de Asistencia)
INSERT INTO Permiso (slug, nombre, modulo, descripcion, estado) VALUES
  ('personas.admin',           'Ver personas',                      'Usuarios',      'Ver el listado de personas registradas.', 'A'),
  ('usuarios.admin',           'Gestionar usuarios',                'Usuarios',      'Crear, editar y desactivar usuarios.', 'A'),
  ('roles.asignar',            'Asignar roles',                     'Usuarios',      'Asignar roles a los usuarios.', 'A'),
  ('roles.admin',              'Gestionar roles',                   'Usuarios',      'Crear y editar roles del sistema.', 'A'),
  ('permisos.admin',           'Gestionar permisos por rol',        'Usuarios',      'Asignar permisos a cada rol.', 'A'),
  ('noticias.admin',           'Publicar noticias',                 'Contenidos',    'Crear, editar y eliminar noticias.', 'A'),
  ('comite.admin',             'Gestionar comité directivo',        'Contenidos',    'Administrar el comité directivo.', 'A'),
  ('historia.admin',           'Gestionar historia y legado',       'Contenidos',    'Administrar la página Historia y Legado.', 'A'),
  ('imagenes.admin',           'Gestionar imágenes del inicio',     'Contenidos',    'Administrar carrusel y talleres del inicio.', 'A'),
  ('nosotros.admin',           'Gestionar página Nosotros',         'Contenidos',    'Administrar el contenido de la página Nosotros.', 'A'),
  ('mesa.admin',               'Ver documentos de mesa de partes',  'Mesa de Partes','Ver y gestionar documentos recibidos.', 'A'),
  ('alumnos.admin',            'Gestionar alumnos',                 'Asistencia',    'Administrar el registro de alumnos.', 'A'),
  ('grados.admin',             'Gestionar grados',                  'Asistencia',    'Administrar los grados.', 'A'),
  ('secciones.admin',          'Gestionar secciones',               'Asistencia',    'Administrar las secciones.', 'A'),
  ('asistencia.registrar',     'Registrar asistencia',              'Asistencia',    'Registrar la asistencia diaria de alumnos.', 'A'),
  ('asistencia.reportes',      'Ver reportes de asistencia',        'Asistencia',    'Ver historial y reportes de asistencia.', 'A'),
  ('asistencia.configurar',    'Configurar horarios de asistencia', 'Asistencia',    'Editar horarios de registro, umbral de reincidencia, límite de días de edición y días no hábiles.', 'A'),
  ('asistencia.editar_vencido','Editar asistencia fuera de plazo',  'Asistencia',    'Corregir un registro de asistencia más allá del límite de días permitido.', 'A');

-- Configuración inicial del módulo de Asistencia
INSERT INTO AsistenciaConfiguracion (hora_apertura, hora_cierre, hora_limite_tardanza, umbral_alertas_mes, dias_limite_edicion) VALUES
  ('05:00:00', '19:00:00', '08:00:00', 3, 7);

INSERT INTO imagenes_inicio (seccion, orden, ruta, alt, titulo, descripcion, icono, activo, created_at, updated_at) VALUES
  ('carousel', 1, 'images/gue.jpg',              'Fachada del colegio',        NULL,              NULL,                                                         NULL,                1, NOW(), NOW()),
  ('carousel', 2, 'images/colegio001.jpg',       'Estudiantes en actividades', NULL,              NULL,                                                         NULL,                1, NOW(), NOW()),
  ('carousel', 3, 'images/colegio003.jpg',       'Instalaciones del campus',   NULL,              NULL,                                                         NULL,                1, NOW(), NOW()),
  ('taller',   1, 'images/talleres/musica.jpg',  'Taller de Música',           'Música',          'Práctica instrumental, ensambles y teoría musical.',        'music-note-beamed', 1, NOW(), NOW()),
  ('taller',   2, 'images/talleres/deporte.jpg', 'Taller de Deporte',          'Deporte',         'Fútbol, vóley y atletismo para todas las categorías.',      'trophy',            1, NOW(), NOW()),
  ('taller',   3, 'images/talleres/pintura.jpg', 'Taller de Artes Plásticas',  'Artes Plásticas', 'Dibujo, pintura y técnicas mixtas.',                        'palette',           1, NOW(), NOW()),
  ('taller',   4, 'images/talleres/danza.jpg',   'Taller de Danza',            'Danza',           'Danza moderna y folclore peruano.',                         'person-arms-up',    1, NOW(), NOW());

-- Contenido inicial de la página "Nosotros"
INSERT INTO NosotrosContenido (clave, valor, created_at, updated_at) VALUES
  ('historia_titulo', 'Nuestra Historia', NOW(), NOW()),
  ('historia_p1', 'La Institución Educativa Emblemática José Faustino Sánchez Carrión es una de las instituciones educativas más prestigiosas de Trujillo, con una larga trayectoria formando generaciones de estudiantes comprometidos con la excelencia académica y los valores ciudadanos.', NOW(), NOW()),
  ('historia_p2', 'A lo largo de nuestra historia, hemos mantenido un firme compromiso con la calidad educativa, adaptándonos a los cambios y necesidades de cada generación sin perder nuestra esencia institucional.', NOW(), NOW()),
  ('historia_imagen', 'images/gue.jpg', NOW(), NOW()),
  ('mision', 'Formar estudiantes con excelencia académica, valores sólidos y compromiso ciudadano, capaces de enfrentar los retos del mundo actual con pensamiento crítico y responsabilidad social.', NOW(), NOW()),
  ('vision', 'Ser reconocidos como una institución educativa líder en la región, referente de calidad académica, formación en valores e innovación pedagógica.', NOW(), NOW()),
  ('pilares', '[{"icon":"heart-fill","titulo":"Valores","desc":"Respeto, responsabilidad, honestidad y solidaridad son la base de nuestra formación integral.","color":"danger"},{"icon":"book-half","titulo":"Excelencia Académica","desc":"Comprometidos con los más altos estándares educativos y la mejora continua.","color":"primary"},{"icon":"shield-check","titulo":"Tradición","desc":"Más de 190 años de historia formando generaciones de líderes trujillanos.","color":"success"},{"icon":"lightbulb-fill","titulo":"Innovación","desc":"Incorporamos tecnología y metodologías modernas para una educación del siglo XXI.","color":"warning"},{"icon":"people-fill","titulo":"Inclusión","desc":"Valoramos la diversidad y garantizamos oportunidades educativas para todos.","color":"info"},{"icon":"star-fill","titulo":"Liderazgo","desc":"Formamos líderes con pensamiento crítico y compromiso social.","color":"dark"}]', NOW(), NOW()),
  ('normas', '["Respetar a todos los miembros de la comunidad educativa","Cumplir con puntualidad y responsabilidad nuestros deberes","Cuidar las instalaciones y materiales educativos","Mantener un ambiente de convivencia armónica y pacífica","Practicar la honestidad en todas nuestras acciones","Valorar y respetar la diversidad cultural","Resolver conflictos mediante el diálogo y la mediación","Contribuir al cuidado del medio ambiente"]', NOW(), NOW());
