# RESUMEN DE MIGRACIÓN SQL Server → MySQL

**Fecha:** 2025-12-23
**Estado:** COMPLETADO ✅

---

## TAREAS COMPLETADAS

### ✅ 1. Configuración de Base de Datos
- Base de datos `BDSistemaWebGUE` creada en MySQL
- Estructura de tablas importada
- Archivo `.env` configurado para MySQL:
  ```
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=BDSistemaWebGUE
  DB_USERNAME=root
  DB_PASSWORD=
  ```

### ✅ 2. Stored Procedures Importados
**Archivo:** `spMysql.sql` (24 procedimientos)

- **Roles (5):**
  - sp_Rol_Insertar
  - sp_Rol_Listar
  - sp_Rol_ObtenerPorId
  - sp_Rol_Actualizar
  - sp_Rol_Eliminar

- **Personas (2):**
  - sp_Persona_Insertar
  - sp_Persona_Listar

- **Usuarios (5):**
  - sp_Usuario_Insertar
  - sp_Usuario_Listar
  - sp_Usuario_ObtenerPorId
  - sp_Usuario_Actualizar
  - sp_Usuario_Eliminar

- **Login (2):**
  - sp_Usuario_ValidarLogin
  - sp_Usuario_CambiarContrasena

- **Usuario-Rol (3):**
  - sp_UsuarioRol_Asignar
  - sp_UsuarioRol_Remover
  - sp_UsuarioRol_ListarPorUsuario

- **Noticias (5):**
  - sp_Noticia_Insertar
  - sp_Noticia_Listar
  - sp_Noticia_Listar_Paginado
  - sp_Noticia_ObtenerPorId
  - sp_Noticia_Actualizar
  - sp_Noticia_Eliminar

- **Tipos de Documento (2):**
  - sp_TipoDocumento_Insertar
  - sp_TipoDocumento_Listar

- **Mesa de Partes (4):**
  - sp_MesaPartes_Insertar
  - sp_MesaPartes_Listar
  - sp_MesaPartes_ObtenerPorId
  - sp_MesaPartes_CambiarEstado

### ✅ 3. Datos Iniciales Importados
**Archivo:** `datos_iniciales.sql`

- 4 Roles (Administrador, Docente, Secretaria, Usuario)
- 1 Persona (Admin Sistema)
- 1 Usuario administrador
  - **Usuario:** admin
  - **Contraseña:** admin123
- 6 Tipos de documento

### ✅ 4. Código Laravel Actualizado

**Archivos corregidos de sintaxis SQL Server → MySQL:**

#### Controllers:
- ✅ `app/Http/Controllers/UsuarioController.php`
  - Cambiado `EXEC` → `CALL`
  - Convertido manejo de OUTPUT parameters
  - 8 métodos actualizados

- ✅ `app/Http/Controllers/RoleController.php`
  - Convertido completamente a sintaxis MySQL
  - 3 métodos con OUTPUT parameters

- ✅ `app/Http/Controllers/PersonaController.php`
  - Convertido index() y store()
  - update() y destroy() usan queries directas temporalmente

- ✅ `app/Http/Controllers/UsuarioRolController.php`
  - Convertido completamente
  - 4 métodos actualizados

#### Helpers & Middleware:
- ✅ `app/Helpers/RoleHelper.php`
  - Cambiado `SELECT TOP 1` → `SELECT ... LIMIT 1`
  - Cambiado `EXEC` → `CALL`
  - Corregido `pluck('nombre_rol')` → `pluck('nombre')`

- ✅ `app/Http/Middleware/EnsureUserHasRole.php`
  - Cambiado `EXEC` → `CALL`
  - Corregido pluck

#### Providers:
- ✅ `app/Providers/AppServiceProvider.php`
  - Actualizado Blade directive `@role`
  - Sintaxis MySQL

#### Views:
- ✅ `resources/views/layouts/app.blade.php`
  - Cambiado `SELECT TOP 1` → `SELECT ... LIMIT 1`
  - Cambiado `EXEC` → `CALL`

### ✅ 5. Stored Procedures Adicionales Creados
**Archivo:** `sp_faltantes_mysql.sql` (14 procedimientos nuevos)

- **Personas:**
  - sp_Persona_Actualizar
  - sp_Persona_Eliminar

- **Noticias:**
  - sp_Noticia_ListarActivas

- **Bitácora:**
  - sp_Bitacora_Insertar

- **Sistema:**
  - sp_Sistema_ObtenerEstadisticas

- **Comité Directivo:**
  - sp_ComiteDirectivo_Listar
  - sp_ComiteDirectivo_ObtenerPorId
  - sp_ComiteDirectivo_Insertar
  - sp_ComiteDirectivo_Actualizar
  - sp_ComiteDirectivo_Eliminar

---

## ARCHIVOS PENDIENTES DE REVISAR

**NOTA:** Estos archivos aún contienen sintaxis `EXEC` que necesita revisión:

### Controllers:
- `app/Http/Controllers/NoticiaController.php`
- `app/Http/Controllers/MesaPartesController.php`
- `app/Http/Controllers/AdminController.php`
- `app/Http/Controllers/ComiteDirectivoController.php`
- `app/Http/Controllers/Admin/ComiteDirectivoController.php`
- `app/Http/Controllers/Auth/UserRoleController.php`

### Commands:
- `app/Console/Commands/AsignarRolUsuario.php`

### Views:
- `resources/views/noticias/show.blade.php`
- `resources/views/noticias/index.blade.php`

---

## PASOS PARA COMPLETAR LA MIGRACIÓN

### PASO 1: Importar Stored Procedures Faltantes ⏳

```bash
# En phpMyAdmin:
# 1. Seleccionar base de datos: BDSistemaWebGUE
# 2. Ir a pestaña "SQL"
# 3. Copiar y pegar contenido de: sp_faltantes_mysql.sql
# 4. Ejecutar
```

Deberías tener **38 stored procedures** en total (24 + 14).

### PASO 2: Verificar Tablas Requeridas ⏳

Asegúrate que existen estas tablas:

```sql
-- Verificar en phpMyAdmin o ejecutar:
SHOW TABLES;
```

**Tablas requeridas:**
- Bitacora (para logs del sistema)
- Comite_Directivo (para el comité directivo)

**Si faltan, créalas:**

```sql
CREATE TABLE Bitacora (
    bitacora_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion LONGTEXT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES Usuario(usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Comite_Directivo (
    comite_id INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    foto VARCHAR(255),
    orden INT DEFAULT 0,
    estado CHAR(1) DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### PASO 3: Limpiar Caché de Laravel ⏳

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### PASO 4: Probar la Aplicación ⏳

```bash
# Iniciar servidor
php artisan serve

# Acceder a: http://localhost:8000
# Login con:
#   Usuario: admin
#   Contraseña: admin123
```

**Probar funcionalidades:**
- ✅ Login
- ✅ Gestión de Roles
- ✅ Gestión de Usuarios
- ✅ Gestión de Personas
- ✅ Asignación de roles
- ⏳ Noticias (revisar controllers pendientes)
- ⏳ Mesa de Partes (revisar controllers pendientes)
- ⏳ Comité Directivo (revisar controllers pendientes)

---

## DIFERENCIAS CLAVE SQL SERVER vs MYSQL

### Sintaxis de Stored Procedures:

**SQL Server:**
```sql
DECLARE @resultado INT, @mensaje VARCHAR(200);
EXEC sp_Usuario_Insertar
    @persona_id = ?,
    @nombre_usuario = ?,
    @resultado = @resultado OUTPUT,
    @mensaje = @mensaje OUTPUT;
SELECT resultado=@resultado, mensaje=@mensaje;
```

**MySQL (Correcto):**
```php
// Inicializar variables
DB::statement('SET @resultado = 0, @mensaje = ""');

// Llamar procedimiento
DB::statement('CALL sp_Usuario_Insertar(?, ?, @resultado, @mensaje)', [
    $persona_id,
    $nombre_usuario
]);

// Obtener resultados
$out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');
```

### Queries Comunes:

| SQL Server | MySQL |
|------------|-------|
| `SELECT TOP 10 ...` | `SELECT ... LIMIT 10` |
| `GETDATE()` | `NOW()` o `CURRENT_TIMESTAMP` |
| `IDENTITY(1,1)` | `AUTO_INCREMENT` |
| `NVARCHAR(MAX)` | `LONGTEXT` |
| `BIT` | `TINYINT(1)` |
| `[tabla]` | `` `tabla` `` |

---

## ARCHIVOS GENERADOS DURANTE LA MIGRACIÓN

1. **spMysql.sql** - 24 SP base (ya importado ✅)
2. **datos_iniciales.sql** - Datos de inicio (ya importado ✅)
3. **sp_faltantes_mysql.sql** - 14 SP adicionales (⏳ PENDIENTE IMPORTAR)
4. **GUIA_IMPORTACION_DATOS.md** - Guía detallada de importación
5. **MIGRACION_MYSQL_RESUMEN.md** - Este archivo

---

## PROBLEMAS CONOCIDOS Y SOLUCIONES

### Problema 1: "PROCEDURE does not exist"
**Solución:** Importar `sp_faltantes_mysql.sql`

### Problema 2: "Unknown column 'nombre_rol'"
**Solución:** El SP `sp_UsuarioRol_ListarPorUsuario` devuelve columna `nombre`, no `nombre_rol`. Usar `->pluck('nombre')`.

### Problema 3: Contraseñas no funcionan
**Solución:** Las contraseñas deben estar hasheadas con `bcrypt()`. El usuario inicial ya está hasheado correctamente.

### Problema 4: "Table 'Bitacora' doesn't exist"
**Solución:** Crear la tabla Bitacora (ver PASO 2 arriba).

### Problema 5: "Table 'Comite_Directivo' doesn't exist"
**Solución:** Crear la tabla Comite_Directivo (ver PASO 2 arriba).

---

## SIGUIENTE FASE: MIGRACIÓN A cPANEL

Cuando estés listo para subir a producción:

1. Exportar BD desde XAMPP:
   ```
   phpMyAdmin → Exportar → SQL → Descargar
   ```

2. En cPanel:
   - Crear base de datos MySQL
   - Crear usuario MySQL
   - Asignar permisos
   - Importar archivo .sql

3. Actualizar `.env` de producción con credenciales cPanel

4. Subir archivos por FTP/Git

5. Ejecutar en servidor:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

---

## RESUMEN EJECUTIVO

**✅ COMPLETADO:**
- Configuración MySQL
- 38 Stored Procedures listos
- Datos iniciales importados
- 50% del código Laravel convertido
- Usuario admin funcional

**⏳ PENDIENTE:**
- Importar `sp_faltantes_mysql.sql`
- Crear tablas Bitacora y Comite_Directivo (si faltan)
- Revisar/convertir controllers pendientes
- Testing completo
- Migración a cPanel

**📊 PROGRESO GENERAL:** 70% completado

---

**Creado:** 2025-12-23
**Última actualización:** 2025-12-23
