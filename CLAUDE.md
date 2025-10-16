# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 9 application (PHP 8.0.2+) for "IE JFSC" (Instituto Educativo) that implements a web management system with role-based access control, news management, and document submission ("Mesa de Partes"). The application uses SQL Server as its database and heavily relies on stored procedures for database operations.

## Technology Stack

- **Backend**: Laravel 9.19 (PHP 8.0.2+)
- **Database**: SQL Server (via `sqlsrv` driver)
- **Frontend**: Blade templates, Vite, Bootstrap Icons
- **Authentication**: Laravel Sanctum
- **Mail**: Configured for SMTP (see `.env` for settings)

## Database Architecture

**Critical**: This application uses SQL Server stored procedures extensively. Database operations are performed through stored procedures, NOT Eloquent ORM queries. Key patterns:

### Stored Procedure Execution Pattern

The application uses a specific pattern for calling stored procedures with OUTPUT parameters:

```php
$sql = "
    DECLARE @resultado INT, @mensaje VARCHAR(200);
    EXEC sp_ProcedureName
        @param1 = ?,
        @param2 = ?,
        @resultado = @resultado OUTPUT,
        @mensaje = @mensaje OUTPUT;
    SELECT resultado = @resultado, mensaje = @mensaje;
";
$out = DB::select($sql, [$value1, $value2]);
$resultado = (int)($out[0]->resultado ?? 0);
$mensaje = (string)($out[0]->mensaje ?? 'Sin mensaje');
```

### Key Stored Procedures

- **Roles**: `sp_Rol_Listar`, `sp_Rol_Insertar`, `sp_Rol_Actualizar`, `sp_Rol_Eliminar`
- **User-Role Assignment**: `sp_UsuarioRol_ListarPorUsuario`, `sp_UsuarioRol_Asignar`
- **News**: `sp_Noticia_Listar`, `sp_Noticia_ObtenerPorId`, `sp_Noticia_Insertar`
- **Mesa de Partes**: `sp_MesaPartes_Listar`, `sp_MesaPartes_ObtenerPorId`, `sp_MesaPartes_ActualizarEstado`
- **Audit Log**: `sp_Bitacora_Insertar`

## Key Models

### Usuario (User)
- Table: `Usuario`
- Primary Key: `usuario_id`
- Custom authentication model extending `Authenticatable`
- Password field: `contrasena` (auto-hashed via mutator)
- Timestamps: disabled
- Relationships: Many-to-many with `Rol` via `UsuarioRol` pivot table

### Rol (Role)
- Table: `Rol`
- Primary Key: `rol_id`
- Core roles: Director, Administrador, Editor
- Timestamps: disabled

### MesaParte (Document Submission)
- Table: `Mesa_Partes`
- Primary Key: `documento_id`
- Stores citizen-submitted documents with file attachments
- States: Pendiente, Revisado, Aceptado, Rechazado
- Timestamps: disabled (uses `fecha_envio` managed by SQL)

### Noticia (News)
- Table: `Noticia`
- Primary Key: `noticia_id`
- Supports image uploads to `storage/app/public/noticias`
- Timestamps: disabled

## Role-Based Access Control

The application implements custom role-based middleware:

### Middleware: `EnsureUserHasRole` (app/Http/Middleware/EnsureUserHasRole.php)
- Alias in routes: `role`
- Usage: `middleware(['auth', 'role:Director,Administrador'])`
- Fetches user roles via `sp_UsuarioRol_ListarPorUsuario` stored procedure
- Case-insensitive role matching

### Role Hierarchy
- **Director/Administrador**: Full admin access to dashboard, roles, user management, Mesa de Partes
- **Editor**: Can create and publish news
- **Public**: Can view news, submit documents to Mesa de Partes

## File Uploads

### Mesa de Partes Documents
- Location: `storage/app/public/mesa_partes/`
- Multiple files supported
- Stored paths concatenated with `; /storage/` separator
- Email notifications sent with attachments to both submitter and admin

### News Images
- Location: `storage/app/public/noticias/`
- Single image per news article

**Important**: Ensure `php artisan storage:link` has been run to create the symbolic link from `public/storage` to `storage/app/public`.

## Common Development Commands

### Artisan Commands
```bash
# Start development server
php artisan serve

# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Create storage symlink (required for file uploads)
php artisan storage:link

# Run migrations (if any exist)
php artisan migrate

# Access Tinker console
php artisan tinker
```

### Composer
```bash
# Install dependencies
composer install

# Update dependencies
composer update

# Dump autoload (after adding new classes)
composer dump-autoload
```

### NPM/Vite (Frontend Assets)
```bash
# Install Node dependencies
npm install

# Development build with hot reload
npm run dev

# Production build
npm run build
```

### Testing
```bash
# Run all tests
php artisan test
# or
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Feature
```

## Routing Structure

Routes are defined in `routes/web.php` with clear sections:

1. **Authentication**: Login/logout routes
2. **Public Routes**: Home, about (`/nosotros`), news listing/detail
3. **Mesa de Partes (Public)**: Document submission form
4. **Admin Routes** (`/admin/*`): Protected by `auth` and `role:Director,Administrador` middleware
   - Dashboard
   - Role CRUD
   - User-role assignment
   - Mesa de Partes management
5. **News Creation**: Protected by `role:Editor,Administrador,Director`

## Environment Configuration

The application requires the following environment variables to be configured in `.env`:

### Database (SQL Server)
```
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_ENCRYPT=no
DB_TRUST_SERVER_CERTIFICATE=false
```

### Mail Configuration
The application sends email notifications for Mesa de Partes submissions. Configure SMTP settings:
```
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email
```

## Known Issues & Quirks

1. **Missing Controller**: `UserRoleController` is referenced in `routes/web.php` but appears to be located at `app/Http/Controllers/Auth/UserRoleController.php`. The namespace may need correction.

2. **RoleController Handle Method**: The `RoleController` contains a `handle()` method (lines 11-44) which appears to be middleware logic but is placed in the controller. This should likely be in a dedicated middleware class.

3. **SQL Server Connection**: Ensure the `sqlsrv` PHP extension is installed and enabled. This is required for SQL Server connectivity.

4. **Output Parameter Pattern**: When working with stored procedures that have OUTPUT parameters, always use the DECLARE -> EXEC -> SELECT pattern shown above. Direct parameter binding with `DB::statement()` does not work reliably with OUTPUT parameters in Laravel.

5. **Timestamps Disabled**: Most models have `public $timestamps = false`. Do not add `created_at`/`updated_at` to fillable arrays or expect them to work.

## Code Style Conventions

- Controllers use stored procedures via `DB::select()` or `DB::statement()`
- Models define relationships but queries go through stored procedures
- Validation messages are in Spanish
- Flash messages use keys: `success`, `error`, `ok`
- File paths stored in database use `/storage/` prefix for public access
