@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Gestión de Usuarios</h1>
        <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus-fill me-2"></i>Crear Usuario
        </a>
    </div>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-white"><strong>Usuarios Registrados</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre de Usuario</th>
                            <th>Persona</th>
                            <th>DNI</th>
                            <th>Correo</th>
                            <th>Estado</th>
                            <th style="width: 250px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($usuarios as $u)
                        <tr>
                            <td>{{ $u->usuario_id }}</td>
                            <td>{{ $u->nombre_usuario }}</td>
                            <td>{{ $u->apellidos }}, {{ $u->nombres }}</td>
                            <td>{{ $u->dni }}</td>
                            <td>{{ $u->correo }}</td>
                            <td>
                                <span class="badge {{ $u->estado === 'A' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $u->estado === 'A' ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.usuarios.edit', $u->usuario_id) }}" class="btn btn-warning btn-sm" title="Editar usuario">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="{{ route('admin.usuarios.change-password', $u->usuario_id) }}" class="btn btn-info btn-sm text-white" title="Cambiar contraseña">
                                        <i class="bi bi-key-fill"></i>
                                    </a>
                                    <form action="{{ route('admin.usuarios.destroy', $u->usuario_id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar el usuario {{ $u->nombre_usuario }}?');" style="display: inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar usuario">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted p-3">No hay usuarios registrados.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
