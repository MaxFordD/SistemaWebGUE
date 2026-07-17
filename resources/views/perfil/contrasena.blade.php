@extends('layouts.app')

@section('title', 'Cambiar mi contraseña')
@section('body_class', 'waves-compact')

@section('content')
<div class="py-4" style="max-width: 480px; margin: 0 auto;">
    <h1 class="h3 fw-bold mb-4">
        <i class="bi bi-key me-2 text-primary"></i>Cambiar contraseña
    </h1>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('perfil.contrasena.update') }}">
                @csrf

                <div class="mb-3">
                    <label for="contrasena_actual" class="form-label fw-semibold">Contraseña actual</label>
                    <input type="password"
                           class="form-control @error('contrasena_actual') is-invalid @enderror"
                           id="contrasena_actual"
                           name="contrasena_actual"
                           autocomplete="current-password"
                           required>
                    @error('contrasena_actual')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="contrasena_nueva" class="form-label fw-semibold">Nueva contraseña</label>
                    <input type="password"
                           class="form-control @error('contrasena_nueva') is-invalid @enderror"
                           id="contrasena_nueva"
                           name="contrasena_nueva"
                           autocomplete="new-password"
                           required
                           minlength="6">
                    @error('contrasena_nueva')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="contrasena_nueva_confirmation" class="form-label fw-semibold">Confirmar nueva contraseña</label>
                    <input type="password"
                           class="form-control"
                           id="contrasena_nueva_confirmation"
                           name="contrasena_nueva_confirmation"
                           autocomplete="new-password"
                           required>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Actualizar contraseña
                    </button>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
