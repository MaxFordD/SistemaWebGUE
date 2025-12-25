-- ===============================================
-- DATOS INICIALES PARA BDSistemaWebGUE
-- Usuario Administrador + Roles Básicos
-- ===============================================

-- 1. ROLES
INSERT INTO Rol (nombre, descripcion, estado) VALUES
('Administrador', 'Acceso total al sistema', 'A'),
('Docente', 'Gestión de contenidos y noticias', 'A'),
('Secretaria', 'Gestión de mesa de partes', 'A'),
('Usuario', 'Acceso de solo lectura', 'A');

-- 2. PERSONA ADMINISTRADOR
INSERT INTO Persona (nombres, apellidos, dni, telefono, correo, estado) VALUES
('Admin', 'Sistema', '00000000', '999999999', 'admin@sistema.com', 'A');

-- 3. USUARIO ADMINISTRADOR
-- Contraseña: admin123
-- Hash generado con bcrypt
INSERT INTO Usuario (persona_id, nombre_usuario, contrasena, estado) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'A');

-- 4. ASIGNAR ROL ADMINISTRADOR AL USUARIO
INSERT INTO UsuarioRol (usuario_id, rol_id) VALUES
(1, 1);

-- 5. TIPOS DE DOCUMENTO (para Mesa de Partes)
INSERT INTO Tipos_Documento (nombre) VALUES
('Solicitud'),
('Reclamo'),
('Consulta'),
('Oficio'),
('Memorándum'),
('Otros');
