# GUÍA DE IMPORTACIÓN DE DATOS ESENCIALES
## SQL Server → MySQL

---

## TABLAS A IMPORTAR (ORDEN IMPORTANTE)

Sigue este orden para respetar las relaciones de claves foráneas:

### 1. **Rol** (Primera - sin dependencias)
### 2. **Persona** (Segunda - sin dependencias)
### 3. **Usuario** (Tercera - depende de Persona)
### 4. **UsuarioRol** (Cuarta - depende de Usuario y Rol)
### 5. **Tipos_Documento** (Opcional - solo si usas Mesa de Partes)

---

## TABLAS QUE NO IMPORTAR AHORA

- ❌ Noticia (lo subirás manualmente después)
- ❌ Mesa_Partes (documentos, lo subirás después)
- ❌ Comite_Directivo (lo subirás después)
- ❌ Bitacora (logs, no es necesario migrar)
- ❌ Mensaje (si existe, lo subirás después)

---

## MÉTODO 1: EXPORTAR DESDE SQL SERVER MANAGEMENT STUDIO

### PASO 1: Exportar cada tabla

Para cada tabla (Rol, Persona, Usuario, UsuarioRol, Tipos_Documento):

1. **Abrir SQL Server Management Studio**
2. **Conectar a tu servidor** (127.0.0.1)
3. **Expandir** `Databases` → `BDSistemaWebGUE` → `Tables`
4. **Click derecho** en la tabla (ej: `dbo.Rol`)
5. **Tasks** → **Generate Scripts...**
6. Click en **Next**
7. Seleccionar **"Select specific database objects"**
8. Marcar **SOLO la tabla actual**
9. Click en **Next**
10. Click en **Advanced** y configurar:
    - **Types of data to script**: `Data only`
    - **Script for Server Version**: `SQL Server 2016` (o menor)
11. Click **OK**
12. **Save to file**: Guardar como `tabla_rol.sql`, `tabla_persona.sql`, etc.
13. Click **Next** → **Finish**

Repetir para cada tabla.

---

## PASO 2: Adaptar los archivos SQL para MySQL

Cada archivo `.sql` exportado tiene sintaxis SQL Server. Debes adaptarlo:

### Abrir cada archivo con editor de texto y hacer estos cambios:

#### **Buscar y Reemplazar:**

```
Buscar: SET IDENTITY_INSERT [dbo].[NombreTabla] ON
Reemplazar: (borrar esta línea)

Buscar: SET IDENTITY_INSERT [dbo].[NombreTabla] OFF
Reemplazar: (borrar esta línea)

Buscar: INSERT [dbo].[NombreTabla]
Reemplazar: INSERT INTO NombreTabla

Buscar: GO
Reemplazar: ; (punto y coma)

Buscar: N'
Reemplazar: '
```

#### **Ejemplo de conversión:**

**SQL Server (antes):**
```sql
SET IDENTITY_INSERT [dbo].[Rol] ON
INSERT [dbo].[Rol] (rol_id, nombre, descripcion, estado) VALUES (1, N'Administrador', N'Acceso total', N'A')
GO
SET IDENTITY_INSERT [dbo].[Rol] OFF
```

**MySQL (después):**
```sql
INSERT INTO Rol (rol_id, nombre, descripcion, estado) VALUES (1, 'Administrador', 'Acceso total', 'A');
```

---

## PASO 3: Importar en MySQL (phpMyAdmin)

Para cada archivo adaptado:

1. **Abrir phpMyAdmin**: `http://localhost/phpmyadmin`
2. **Seleccionar** la base de datos `BDSistemaWebGUE`
3. **Ir a pestaña "SQL"**
4. **Copiar y pegar** el contenido del archivo adaptado
5. **Click en "Continuar"**
6. **Verificar** mensaje verde de éxito

### **ORDEN DE IMPORTACIÓN (IMPORTANTE):**

```
1º → tabla_rol.sql
2º → tabla_persona.sql
3º → tabla_usuario.sql
4º → tabla_usuariorol.sql
5º → tabla_tipos_documento.sql (si lo usas)
```

---

## MÉTODO 2: SCRIPT SQL MANUAL (Alternativa rápida)

Si tienes pocos registros, puedes crear manualmente los INSERT:

### Ejemplo para tabla Rol:

```sql
-- Primero ver qué tienes en SQL Server:
-- SELECT * FROM Rol

-- Luego crear los INSERT para MySQL:
INSERT INTO Rol (rol_id, nombre, descripcion, estado) VALUES
(1, 'Administrador', 'Acceso total al sistema', 'A'),
(2, 'Docente', 'Acceso a gestión de contenidos', 'A'),
(3, 'Usuario', 'Acceso básico de consulta', 'A');
```

### Ejemplo para tabla Persona:

```sql
INSERT INTO Persona (persona_id, nombres, apellidos, dni, telefono, correo, estado) VALUES
(1, 'Juan', 'Pérez García', '12345678', '987654321', 'juan@example.com', 'A');
```

### Ejemplo para tabla Usuario:

```sql
-- IMPORTANTE: Las contraseñas deben estar hasheadas
INSERT INTO Usuario (usuario_id, persona_id, nombre_usuario, contrasena, estado) VALUES
(1, 1, 'admin', '$2y$10$...hash...', 'A');
```

### Ejemplo para tabla UsuarioRol:

```sql
INSERT INTO UsuarioRol (usuario_id, rol_id) VALUES
(1, 1);  -- Usuario 1 tiene Rol 1 (Administrador)
```

---

## VERIFICACIÓN DESPUÉS DE IMPORTAR

Ejecuta estos comandos en Laravel para verificar:

```bash
# Ver cuántos roles importaste
php artisan tinker --execute="echo 'Roles: ' . DB::table('rol')->count();"

# Ver cuántas personas importaste
php artisan tinker --execute="echo 'Personas: ' . DB::table('persona')->count();"

# Ver cuántos usuarios importaste
php artisan tinker --execute="echo 'Usuarios: ' . DB::table('usuario')->count();"

# Ver roles asignados
php artisan tinker --execute="echo 'Asignaciones: ' . DB::table('usuariorol')->count();"
```

---

## IMPORTANTE: CONTRASEÑAS

### Si las contraseñas están en texto plano en SQL Server:

**NO las migres así.** Debes hashearlas primero.

**Opción 1:** Crear usuario nuevo desde Laravel después de migrar
```bash
php artisan tinker
```
```php
$persona = DB::table('persona')->first();
DB::table('usuario')->insert([
    'persona_id' => $persona->persona_id,
    'nombre_usuario' => 'admin',
    'contrasena' => bcrypt('tu_contraseña_segura'),
    'estado' => 'A'
]);
```

**Opción 2:** Si ya están hasheadas, migrarlas directamente

---

## DATOS MÍNIMOS RECOMENDADOS

Para que el sistema funcione, necesitas al menos:

### 1. Un rol de Administrador:
```sql
INSERT INTO Rol (nombre, descripcion, estado) VALUES
('Administrador', 'Acceso total', 'A');
```

### 2. Una persona:
```sql
INSERT INTO Persona (nombres, apellidos, dni, correo, estado) VALUES
('Admin', 'Sistema', '00000000', 'admin@sistema.com', 'A');
```

### 3. Un usuario admin:
```sql
-- Obtener el ID de la persona creada
-- Luego insertar usuario con contraseña hasheada
```

### 4. Asignar rol:
```sql
-- Relacionar usuario con rol administrador
INSERT INTO UsuarioRol (usuario_id, rol_id) VALUES (1, 1);
```

---

## ORDEN DE EJECUCIÓN COMPLETO

```
✅ 1. Importar Stored Procedures (spMysql.sql) → YA HECHO
✅ 2. Importar estructura de tablas → YA HECHO
⏳ 3. Importar datos de Rol
⏳ 4. Importar datos de Persona
⏳ 5. Importar datos de Usuario
⏳ 6. Importar datos de UsuarioRol
⏳ 7. Importar datos de Tipos_Documento (opcional)
⏭️ 8. Noticias, Mesa_Partes, Comité → Lo subirás después manualmente
```

---

## ERRORES COMUNES

### Error: "Duplicate entry for key PRIMARY"
- **Causa:** Intentas insertar un ID que ya existe
- **Solución:** Verifica que la tabla esté vacía antes de importar

### Error: "Cannot add or update a child row: foreign key constraint fails"
- **Causa:** Intentas insertar en orden incorrecto
- **Solución:** Sigue el orden: Rol → Persona → Usuario → UsuarioRol

### Error: "Incorrect string value"
- **Causa:** Problema de encoding
- **Solución:** Asegúrate que las tablas usen `utf8mb4_unicode_ci`

---

## SIGUIENTE PASO

Después de importar estos datos esenciales:

1. Cambiar `EXEC` por `CALL` en código Laravel (pendiente)
2. Completar stored procedures faltantes (pendiente)
3. Probar login y funcionalidad básica
4. Luego subir noticias y demás contenido manualmente

---

**Fecha de creación:** 2025-12-23
**Para:** Migración BDSistemaWebGUE - SQL Server → MySQL
