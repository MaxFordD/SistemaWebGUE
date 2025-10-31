@extends('layouts.app')

@section('title', 'Usuarios con roles')

@section('content')
<div class="py-4">
    <h1 class="h4 mb-3">Usuarios — Roles</h1>

    <form action="{{ route('admin.roles.users') }}" method="GET" class="row g-2 mb-3">
        <div class="col-12 col-md-6">
            <label class="form-label">Usuario</label>
            <select name="usuario_id" class="form-select" onchange="this.form.submit()">
                <option value="">-- Selecciona --</option>
                @foreach($usuarios as $u)
                    <option value="{{ $u->usuario_id }}" {{ (int)($usuarioId ?? 0) === (int)$u->usuario_id ? 'selected' : '' }}>
                        [{{ $u->usuario_id }}] {{ $u->apellidos }}, {{ $u->nombres }} — {{ $u->nombre_usuario }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="card">
        <div class="card-header bg-white"><strong>Roles del usuario</strong></div>
        <div class="card-body p-0">
            @if(!($usuarioId))
                <div class="p-3 text-muted">Selecciona un usuario.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID Rol</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($rolesUsuario as $r)
                            <tr>
                                <td>{{ $r->rol_id }}</td>
                                <td>{{ $r->nombre_rol }}</td>
                                <td>{{ $r->descripcion }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted p-3">Sin roles asignados.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
