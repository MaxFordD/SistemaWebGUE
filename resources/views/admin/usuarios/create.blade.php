@extends('layouts.app')

@section('title', 'Crear Usuario')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Crear Usuario</h1>
        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">Volver</a>
    </div>

    @if (session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-white"><strong>Datos del Usuario</strong></div>
        <div class="card-body">
            <form action="{{ route('admin.usuarios.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">Persona *</label>
                        <select name="persona_id" class="form-select" required>
                            <option value="">Seleccione una persona</option>
                            @foreach($personas as $p)
                                <option value="{{ $p->persona_id }}" {{ old('persona_id') == $p->persona_id ? 'selected' : '' }}>
                                    {{ $p->apellidos }}, {{ $p->nombres }} - DNI: {{ $p->dni }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Seleccione la persona a la que se le creará el usuario</small>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">Nombre de Usuario *</label>
                        <input type="text" name="nombre_usuario" class="form-control" value="{{ old('nombre_usuario') }}" maxlength="100" required>
                        <small class="text-muted">Usuario único para iniciar sesión</small>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">Contraseña *</label>
                        <input type="password" name="contrasena" class="form-control" minlength="6" required>
                        <small class="text-muted">Mínimo 6 caracteres</small>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">Confirmar Contraseña *</label>
                        <input type="password" name="contrasena_confirmation" class="form-control" minlength="6" required>
                        <small class="text-muted">Repita la contraseña</small>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Crear Usuario</button>
                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
