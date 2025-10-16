@extends('layouts.app')

@section('title', 'Asignar Roles a Usuarios')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Asignar Roles a Usuarios</h1>
    </div>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <div class="card">
        <div class="card-header bg-white"><strong>Seleccione un Usuario</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Persona</th>
                            <th>DNI</th>
                            <th>Estado</th>
                            <th style="width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($usuarios as $u)
                        <tr>
                            <td>{{ $u->usuario_id }}</td>
                            <td>{{ $u->nombre_usuario }}</td>
                            <td>{{ $u->apellidos }}, {{ $u->nombres }}</td>
                            <td>{{ $u->dni }}</td>
                            <td>
                                <span class="badge {{ $u->estado === 'A' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $u->estado === 'A' ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.usuario-rol.show', $u->usuario_id) }}" class="btn btn-sm btn-primary">
                                    Gestionar Roles
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted p-3">No hay usuarios registrados.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
