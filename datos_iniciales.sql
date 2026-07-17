-- ===============================================
-- DATOS INICIALES PARA BDSistemaWebGUE
-- Usuario Administrador + Roles Básicos
-- ===============================================

-- 1. ROLES
INSERT IGNORE INTO Rol (nombre, descripcion, estado) VALUES
('Administrador', 'Acceso total al sistema', 'A'),
('Docente', 'Gestión de contenidos y noticias', 'A'),
('Secretaria', 'Gestión de mesa de partes', 'A'),
('Usuario', 'Acceso de solo lectura', 'A');

-- 2. PERSONA ADMINISTRADOR
INSERT IGNORE INTO Persona (nombres, apellidos, dni, telefono, correo, estado) VALUES
('Admin', 'Sistema', '00000000', '999999999', 'admin@sistema.com', 'A');

-- 3. USUARIO ADMINISTRADOR
-- Contraseña: admin123
INSERT IGNORE INTO Usuario (persona_id, nombre_usuario, contrasena, estado)
SELECT p.persona_id, 'admin', '$2y$10$goOXn.NqdF4ORC9uLtpNl.Rm1aTS.AFxjg3t8af/7f3SlRQCCxdfq', 'A'
FROM Persona p
WHERE p.dni = '00000000';

-- 4. ASIGNAR ROL ADMINISTRADOR AL USUARIO
-- (se busca por nombre, no por ID, para no depender del orden de inserción de roles)
INSERT IGNORE INTO UsuarioRol (usuario_id, rol_id)
SELECT u.usuario_id, r.rol_id
FROM Usuario u
CROSS JOIN Rol r
WHERE u.nombre_usuario = 'admin' AND r.nombre = 'Administrador';

-- 5. TIPOS DE DOCUMENTO (para Mesa de Partes)
INSERT IGNORE INTO Tipos_Documento (nombre) VALUES
('Solicitud'),
('Reclamo'),
('Consulta'),
('Oficio'),
('Memorándum'),
('Otros');
