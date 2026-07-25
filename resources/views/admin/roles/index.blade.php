@extends('layouts.app')

@section('title', 'Roles')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Roles</h1>
        <a href="{{ route('admin.usuario-rol.index') }}" class="btn btn-sm btn-outline-primary">Asignar Roles a Usuarios</a>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white"><strong>Nuevo rol</strong></div>
        <div class="card-body">
            <form action="{{ route('admin.roles.store') }}" method="POST" class="row g-2">
                @csrf
                <div class="col-12 col-md-4">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" class="form-control" maxlength="50" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="descripcion" value="{{ old('descripcion') }}" class="form-control" maxlength="200">
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
                    @php $rolesProtegidos = ['administrador', 'director']; @endphp
                    @forelse($roles as $r)
                        @php $esProtegido = in_array(mb_strtolower(trim($r->nombre)), $rolesProtegidos); @endphp
                        <tr class="{{ $r->estado === 'I' ? 'text-muted' : '' }}">
                            <td>{{ $r->rol_id }}</td>
                            <td>
                                <form action="{{ route('admin.roles.update', $r->rol_id) }}" method="POST" class="row g-1">
                                    @csrf
                                    <div class="col-12 col-md-5">
                                        <input name="nombre" class="form-control form-control-sm" value="{{ $r->nombre }}" maxlength="50" required {{ $esProtegido ? 'readonly title=Este rol no se puede renombrar' : '' }}>
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <input name="descripcion" class="form-control form-control-sm" value="{{ $r->descripcion }}" maxlength="200">
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <select name="estado" class="form-select form-select-sm" {{ $esProtegido ? 'disabled' : '' }}>
                                            <option value="A" {{ $r->estado === 'A' ? 'selected' : '' }}>Activo</option>
                                            <option value="I" {{ $r->estado === 'I' ? 'selected' : '' }}>Inactivo</option>
                                        </select>
                                        @if($esProtegido)
                                            {{-- El select va deshabilitado (no se envía); se manda el valor real igual --}}
                                            <input type="hidden" name="estado" value="{{ $r->estado }}">
                                        @endif
                                    </div>
                                    <div class="col-12 mt-2">
                                        <span class="badge {{ $r->estado === 'A' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $r->estado === 'A' ? 'Activo' : 'Inactivo' }}
                                        </span>
                                        <button class="btn btn-sm btn-outline-primary">Guardar</button>
                                    </div>
                                </form>
                            </td>
                            <td class="d-none d-md-table-cell"></td>
                            <td class="d-none d-md-table-cell"></td>
                            <td>
                                @if($esProtegido)
                                    <span class="text-muted small" title="Este rol es necesario para el funcionamiento del sistema">
                                        <i class="bi bi-shield-lock"></i> Protegido
                                    </span>
                                @elseif($r->estado === 'I')
                                    <form action="{{ route('admin.roles.force-destroy', $r->rol_id) }}" method="POST" onsubmit="return confirm('Esto borra el rol \'{{ addslashes($r->nombre) }}\' de forma DEFINITIVA (no se puede deshacer). Solo funciona si nadie lo usa. ¿Continuar?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="Eliminar definitivamente">
                                            <i class="bi bi-trash-fill"></i> Eliminar
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.roles.destroy', $r->rol_id) }}" method="POST" onsubmit="return confirm('Esto desactiva el rol \'{{ addslashes($r->nombre) }}\' (no lo borra de la base de datos, solo deja de poder usarse). Si tiene usuarios asignados, no se podrá desactivar hasta quitárselo a todos. ¿Continuar?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Desactivar</button>
                                    </form>
                                @endif
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
