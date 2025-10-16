@extends('layouts.app')

@section('title', 'Roles')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Roles</h1>
        <div class="btn-group">
            <a href="{{ route('admin.roles.assign') }}" class="btn btn-sm btn-outline-primary">Asignar rol</a>
            <a href="{{ route('admin.roles.users') }}" class="btn btn-sm btn-outline-secondary">Usuarios con roles</a>
        </div>
    </div>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header bg-white"><strong>Nuevo rol</strong></div>
        <div class="card-body">
            <form action="{{ route('admin.roles.store') }}" method="POST" class="row g-2">
                @csrf
                <div class="col-12 col-md-4">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" maxlength="50" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="descripcion" class="form-control" maxlength="200">
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Crear</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white"><strong>Roles existentes</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th style="width: 220px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($roles as $r)
                        <tr>
                            <td>{{ $r->rol_id }}</td>
                            <td>
                                <form action="{{ route('admin.roles.update', $r->rol_id) }}" method="POST" class="row g-1">
                                    @csrf
                                    <div class="col-12 col-md-5">
                                        <input name="nombre" class="form-control form-control-sm" value="{{ $r->nombre }}" maxlength="50" required>
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <input name="descripcion" class="form-control form-control-sm" value="{{ $r->descripcion }}" maxlength="200">
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <select name="estado" class="form-select form-select-sm">
                                            <option value="A" {{ $r->estado === 'A' ? 'selected' : '' }}>Activo</option>
                                            <option value="I" {{ $r->estado === 'I' ? 'selected' : '' }}>Inactivo</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <button class="btn btn-sm btn-outline-primary">Guardar</button>
                                    </div>
                                </form>
                            </td>
                            <td class="d-none d-md-table-cell"></td>
                            <td class="d-none d-md-table-cell"></td>
                            <td>
                                <form action="{{ route('admin.roles.destroy', $r->rol_id) }}" method="POST" onsubmit="return confirm('¿Eliminar (lógico) este rol?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted p-3">Sin roles.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
