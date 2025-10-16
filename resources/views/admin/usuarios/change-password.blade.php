@extends('layouts.app')

@section('title', 'Cambiar Contraseña')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Cambiar Contraseña</h1>
        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">Volver</a>
    </div>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-white"><strong>Cambiar Contraseña de Usuario</strong></div>
        <div class="card-body">
            <div class="mb-3">
                <p><strong>Usuario:</strong> {{ $usuario->nombre_usuario }}</p>
                <p><strong>Persona:</strong> {{ $usuario->apellidos }}, {{ $usuario->nombres }}</p>
            </div>
            <hr>
            <form action="{{ route('admin.usuarios.update-password', $usuario->usuario_id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">Contraseña Actual *</label>
                        <input type="password" name="contrasena_actual" class="form-control" required>
                    </div>

                    <div class="col-12"></div>

                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">Contraseña Nueva *</label>
                        <input type="password" name="contrasena_nueva" class="form-control" minlength="6" required>
                        <small class="text-muted">Mínimo 6 caracteres</small>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">Confirmar Contraseña Nueva *</label>
                        <input type="password" name="contrasena_nueva_confirmation" class="form-control" minlength="6" required>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
