# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

"Sistema Web GUE" — a Laravel 9 (PHP 8.1) intranet/public site for a school (I.E. JFSC), used from `resources/views` (Blade + Bootstrap 5, no SPA framework). Public pages (home, noticias, nosotros, mesa de partes, comité directivo) coexist with an authenticated `/admin` back-office for staff (asistencia/attendance, alumnos/students, usuarios, roles, mesa de partes management, content management).

## Commands

- Install deps: `composer install` and `npm install`
- Dev server: `php artisan serve` (backend) + `npm run dev` (Vite, for `resources/css/app.css` and `resources/js/app.js` only — most CSS/JS actually lives unbundled in `public/css` and `public/js`, linked directly in Blade views)
- Build frontend assets: `npm run build`
- Run migrations (includes stored procedure creation, see below): `php artisan migrate`
- Tests: `php artisan test` or `vendor/bin/phpunit`; single test: `vendor/bin/phpunit --filter testName tests/Feature/SomeTest.php`
- Tinker: `php artisan tinker`
- Assign a role to a user from CLI: `php artisan` command `AsignarRolUsuario` (see `app/Console/Commands`)
- Create the public storage symlink (required for uploaded images to be servable): `php artisan storage:link`

## Architecture

### Data access is stored-procedure-first, not Eloquent-first

Most read/write logic goes through MySQL stored procedures called via `DB::select('CALL sp_X_Y(?, ...)', [...])` or `DB::unprepared(...)`, **not** through Eloquent query building or relationships. Procedures are created by migrations named like `database/migrations/2026_05_25_000001_create_stored_procedures.php`, following the naming convention `sp_<Entity>_<Action>` (e.g. `sp_Alumno_Insertar`, `sp_Seccion_ListarActivas`, `sp_UsuarioRol_ListarPorUsuario`). When adding a new feature that needs new queries, check whether a matching SP already exists before writing raw Eloquent/query-builder code, and if adding new persistence logic, prefer adding a new SP migration consistent with this pattern over ad-hoc queries — this keeps validation/business rules centralized in the DB layer the way the rest of the app expects.

`BDSISTEMAWEBGUE_mysql.sql` and `datos_iniciales.sql` at the repo root are reference dumps of the schema/seed data — consult them to see full table structure and existing procedures, but the migrations are the source of truth for what actually gets applied.

Eloquent models exist (`app/Models`) mainly for auth (`Usuario`, via `Illuminate\Foundation\Auth\User`) and a handful of simpler tables (`Persona`, `Noticia`, `MesaParte`, `Rol`); even these are often bypassed by raw `DB::select`/`DB::table` calls in controllers for anything non-trivial.

### Auth and roles

- The auth guard's user model is `App\Models\Usuario` (table `Usuario`, PK `usuario_id`, password column `contrasena`). Login state does not use Eloquent relationships for authorization.
- Roles are resolved per-request by calling the SP `sp_UsuarioRol_ListarPorUsuario(usuario_id)` — see `app/Http/Middleware/EnsureUserHasRole.php` (route middleware alias `role:`, e.g. `->middleware('role:Director,Administrador')`) and `app/Helpers/RoleHelper.php` (for role checks inside views/controllers, e.g. `RoleHelper::isAdmin()`, `RoleHelper::canManageMesaPartes()`). Role names are compared case-insensitively.
- Route groups in `routes/web.php` are organized by required role combination (e.g. `role:Director,Administrador`, `role:Director,Administrador,Auxiliar`, `role:Secretaria,Editor,Administrador,Director`) — when adding an admin route, place it in the group matching who should access it, or start a new `Route::middleware(['auth','role:...'])->prefix('admin')->name('admin.')->group(...)` block.
- User actions are audited in a `Bitacora` table (see `app/Http/Controllers/Admin/BitacoraController.php`), joined against `Usuario` for the actor's name.

### File uploads

`app/Services/ArchivoService.php` centralizes file storage: files are saved to `storage/app/public/<dir>` via the `public` disk (served through the `php artisan storage:link` symlink at `public/storage`), and multiple files for one record are persisted as a single string column with paths joined by `; ` (e.g. `/storage/x.jpg; /storage/y.jpg`) rather than a related table — use `ArchivoService::concatenarRutas()`/`extraerImagenes()`/`eliminarArchivos()` to work with these fields instead of re-implementing the join/split logic. It also has `optimizarImagen()` to downscale/recompress images in place with GD (jpg/png/webp only, no-op if GD isn't available).

### Exports and QR

- Excel exports use `maatwebsite/excel` — export classes live in `app/Exports` (`AlumnoExport`, `AsistenciaExport`, `AsistenciaAlumnoExport`) and are fed pre-fetched SP result collections, not query builders.
- PDF reports use `barryvdh/laravel-dompdf` (blade views like `admin/asistencia/reporte-pdf.blade.php`).
- Student QR codes use `simplesoftwareio/simple-qrcode`, tied to a `codigo_qr` column on `Alumno` (added in a later migration) and scanned in the attendance flow (`AsistenciaController::escanear`).

### Frontend

No SPA/build-driven component system. Vite (`resources/js/app.js`, `resources/css/app.css`) only wraps Bootstrap/Popper/axios bootstrapping; the bulk of page-specific CSS/JS is plain files under `public/css/*.css` and `public/js/*.js`, referenced directly by `<link>`/`<script>` tags per Blade view. `resources/views/layouts/app.blade.php` is the shared admin/public layout shell. TinyMCE (`public/js/tinymce-init.js`) is used for rich text (noticias, historia-legado).

## Language

The codebase, UI copy, commit messages, and code comments are in Spanish (Peruvian schooling domain terms: alumno, seccion, grado, asistencia, mesa de partes, etc.) — match this when adding code, comments, or user-facing strings.
