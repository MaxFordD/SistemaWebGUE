@extends('layouts.app')

@section('title', 'Gestión de Personas')

@push('styles')
<style>
    .modal {
        z-index: 1055 !important;
    }
    .modal-backdrop {
        z-index: 1050 !important;
    }
</style>
@endpush

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Gestión de Personas</h1>
    </div>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header bg-white"><strong>Nueva Persona</strong></div>
        <div class="card-body">
            <form action="{{ route('admin.personas.store') }}" method="POST" class="row g-2">
                @csrf
                <div class="col-12 col-md-3">
                    <label class="form-label">Nombres *</label>
                    <input type="text" name="nombres" class="form-control" maxlength="100" required>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Apellidos *</label>
                    <input type="text" name="apellidos" class="form-control" maxlength="100" required>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">DNI</label>
                    <input type="text" name="dni" class="form-control" maxlength="8" pattern="[0-9]{8}">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" maxlength="9" pattern="[0-9]{9}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" class="form-control" maxlength="100">
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Registrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white"><strong>Personas Registradas</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>DNI</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Estado</th>
                            <th style="width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($personas as $p)
                        <tr>
                            <td>{{ $p->persona_id }}</td>
                            <td>{{ $p->nombres }}</td>
                            <td>{{ $p->apellidos }}</td>
                            <td>{{ $p->dni }}</td>
                            <td>{{ $p->telefono }}</td>
                            <td>{{ $p->correo }}</td>
                            <td>
                                <span class="badge {{ $p->estado === 'A' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $p->estado === 'A' ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $p->persona_id }}">
                                        Editar
                                    </button>
                                    <form action="{{ route('admin.personas.destroy', $p->persona_id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta persona?');" style="display: inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-muted p-3">No hay personas registradas.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Modales de Edición (al final, fuera del content) -->
@foreach($personas as $p)
<div class="modal fade" id="editModal{{ $p->persona_id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $p->persona_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel{{ $p->persona_id }}">Editar Persona #{{ $p->persona_id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="{{ route('admin.personas.update', $p->persona_id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombres *</label>
                        <input type="text" name="nombres" class="form-control" value="{{ $p->nombres }}" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Apellidos *</label>
                        <input type="text" name="apellidos" class="form-control" value="{{ $p->apellidos }}" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">DNI</label>
                        <input type="text" name="dni" class="form-control" value="{{ $p->dni }}" maxlength="8" pattern="[0-9]{8}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="{{ $p->telefono }}" maxlength="9" pattern="[0-9]{9}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" name="correo" class="form-control" value="{{ $p->correo }}" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estado *</label>
                        <select name="estado" class="form-select" required>
                            <option value="A" {{ $p->estado === 'A' ? 'selected' : '' }}>Activo</option>
                            <option value="I" {{ $p->estado === 'I' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
