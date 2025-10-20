<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\UserRoleController;
use App\Http\Controllers\ComiteDirectivoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\MesaPartesController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UsuarioRolController;

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

/*
|--------------------------------------------------------------------------
| Público
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/nosotros', fn() => view('nosotros'))->name('nosotros');
Route::get('/comite-directivo', [ComiteDirectivoController::class, 'index'])->name('comite-directivo');

/*
|--------------------------------------------------------------------------
| Mesa de Partes (público)
|--------------------------------------------------------------------------
*/

Route::get('/mesa-partes/create', [MesaPartesController::class, 'create'])->name('mesa.create');
Route::post('/mesa-partes', [MesaPartesController::class, 'store'])->name('mesa.store');

/*
|--------------------------------------------------------------------------
| Noticias (público: listado y detalle)
|--------------------------------------------------------------------------
*/

Route::get('/noticias', [NoticiaController::class, 'index'])->name('noticias.index');
Route::get('/noticias/{id}', [NoticiaController::class, 'show'])
	->whereNumber('id')
	->name('noticias.show');

/*
|--------------------------------------------------------------------------
| Administración (requiere autenticación y rol Director/Administrador)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Director,Administrador'])->prefix('admin')->name('admin.')->group(function () {
	// Dashboard
	Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

	/*
	|--------------------------------------------------------------------------
	| Roles (CRUD basado en SP)
	|--------------------------------------------------------------------------
	*/
	Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
	Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
	Route::post('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
	Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');

	/*
	|--------------------------------------------------------------------------
	| Asignación de roles a usuarios (compatibilidad con código anterior)
	|--------------------------------------------------------------------------
	*/
	Route::get('/roles/asignar', [UserRoleController::class, 'create'])->name('roles.assign');
	Route::post('/roles/asignar', [UserRoleController::class, 'store'])->name('roles.assign.store');
	Route::get('/roles/usuarios', [UserRoleController::class, 'index'])->name('roles.users');

	/*
	|--------------------------------------------------------------------------
	| Gestión de Personas (CRUD basado en SP)
	|--------------------------------------------------------------------------
	*/
	Route::get('/personas', [PersonaController::class, 'index'])->name('personas.index');
	Route::post('/personas', [PersonaController::class, 'store'])->name('personas.store');
	Route::post('/personas/{id}', [PersonaController::class, 'update'])->name('personas.update');
	Route::delete('/personas/{id}', [PersonaController::class, 'destroy'])->name('personas.destroy');

	/*
	|--------------------------------------------------------------------------
	| Gestión de Usuarios (CRUD basado en SP)
	|--------------------------------------------------------------------------
	*/
	Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
	Route::get('/usuarios/create', [UsuarioController::class, 'create'])->name('usuarios.create');
	Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
	Route::get('/usuarios/{id}/edit', [UsuarioController::class, 'edit'])->name('usuarios.edit');
	Route::post('/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
	Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
	Route::get('/usuarios/{id}/change-password', [UsuarioController::class, 'changePassword'])->name('usuarios.change-password');
	Route::post('/usuarios/{id}/change-password', [UsuarioController::class, 'updatePassword'])->name('usuarios.update-password');

	/*
	|--------------------------------------------------------------------------
	| Asignación de Roles a Usuarios (Nueva interfaz)
	|--------------------------------------------------------------------------
	*/
	Route::get('/usuario-rol', [UsuarioRolController::class, 'index'])->name('usuario-rol.index');
	Route::get('/usuario-rol/{usuarioId}', [UsuarioRolController::class, 'show'])->name('usuario-rol.show');
	Route::post('/usuario-rol/asignar', [UsuarioRolController::class, 'asignar'])->name('usuario-rol.asignar');
	Route::post('/usuario-rol/remover', [UsuarioRolController::class, 'remover'])->name('usuario-rol.remover');

	/*
	|--------------------------------------------------------------------------
	| Administración - Mesa de Partes
	|--------------------------------------------------------------------------
	*/
	Route::get('/mesa-partes', [MesaPartesController::class, 'index'])->name('mesa.index');
	Route::get('/mesa-partes/{id}', [MesaPartesController::class, 'show'])->name('mesa.show');
	Route::post('/mesa-partes/{id}/estado', [MesaPartesController::class, 'updateEstado'])->name('mesa.estado');
	Route::delete('/mesa-partes/{id}', [MesaPartesController::class, 'destroy'])->name('mesa.destroy');

	/*
	|--------------------------------------------------------------------------
	| Administración - Comité Directivo
	|--------------------------------------------------------------------------
	*/
	Route::get('/comite-directivo', [\App\Http\Controllers\Admin\ComiteDirectivoController::class, 'index'])->name('comite-directivo.index');
	Route::get('/comite-directivo/inactivos', [\App\Http\Controllers\Admin\ComiteDirectivoController::class, 'inactivos'])->name('comite-directivo.inactivos');
	Route::get('/comite-directivo/create', [\App\Http\Controllers\Admin\ComiteDirectivoController::class, 'create'])->name('comite-directivo.create');
	Route::post('/comite-directivo', [\App\Http\Controllers\Admin\ComiteDirectivoController::class, 'store'])->name('comite-directivo.store');
	Route::get('/comite-directivo/{id}/edit', [\App\Http\Controllers\Admin\ComiteDirectivoController::class, 'edit'])->name('comite-directivo.edit');
	Route::put('/comite-directivo/{id}', [\App\Http\Controllers\Admin\ComiteDirectivoController::class, 'update'])->name('comite-directivo.update');
	Route::delete('/comite-directivo/{id}', [\App\Http\Controllers\Admin\ComiteDirectivoController::class, 'destroy'])->name('comite-directivo.destroy');
	Route::post('/comite-directivo/{id}/restore', [\App\Http\Controllers\Admin\ComiteDirectivoController::class, 'restore'])->name('comite-directivo.restore');
});

/*
|--------------------------------------------------------------------------
| Noticias (crear/publicar) - Editor, Administrador y Director
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Editor,Administrador,Director'])->group(function () {
	Route::get('/noticias/create', [NoticiaController::class, 'create'])->name('noticias.create');
	Route::post('/noticias', [NoticiaController::class, 'store'])->name('noticias.store');
	Route::get('/noticias/{id}/edit', [NoticiaController::class, 'edit'])->whereNumber('id')->name('noticias.edit');
	Route::put('/noticias/{id}', [NoticiaController::class, 'update'])->whereNumber('id')->name('noticias.update');
	Route::delete('/noticias/{id}', [NoticiaController::class, 'destroy'])->whereNumber('id')->name('noticias.destroy');
});
