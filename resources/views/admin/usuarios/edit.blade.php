@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Editar Usuario</h1>
        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">Volver</a>
    </div>

    <div class="card">
        <div class="card-header bg-white"><strong>Datos del Usuario</strong></div>
        <div class="card-body">
            <form action="{{ route('admin.usuarios.update', $usuario->usuario_id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">Persona Asociada</label>
                        <input type="text" class="form-control" value="{{ $usuario->apellidos }}, {{ $usuario->nombres }}" readonly disabled>
                        <small class="text-muted">No se puede modificar la persona asociada</small>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">DNI</label>
                        <input type="text" class="form-control" value="{{ $usuario->dni }}" readonly disabled>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">Nombre de Usuario *</label>
                        <input type="text" name="nombre_usuario" class="form-control" value="{{ old('nombre_usuario', $usuario->nombre_usuario) }}" maxlength="100" required>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">Estado *</label>
                        <select name="estado" class="form-select" required>
                            <option value="A" {{ old('estado', $usuario->estado) === 'A' ? 'selected' : '' }}>Activo</option>
                            <option value="I" {{ old('estado', $usuario->estado) === 'I' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
