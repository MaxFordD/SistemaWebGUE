@extends('layouts.app')

@section('title', 'Asignar rol a usuario')

@section('content')
<div class="py-4">
    <h1 class="h4 mb-3">Asignar  2 rol a usuario</h1>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('admin.roles.assign') }}" method="GET" class="row g-2 mb-3">
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

    <div class="card mb-4">
        <div class="card-header bg-white"><strong>Asignar nuevo rol</strong></div>
        <div class="card-body">
            <form action="{{ route('admin.roles.assign.store') }}" method="POST" class="row g-2">
                @csrf
                <div class="col-12 col-md-6">
                    <label class="form-label">Usuario *</label>
                    <select name="usuario_id" class="form-select" required>
                        <option value="">-- Selecciona --</option>
                        @foreach($usuarios as $u)
                            <option value="{{ $u->usuario_id }}" {{ (int)($usuarioId ?? 0) === (int)$u->usuario_id ? 'selected' : '' }}>
                                [{{ $u->usuario_id }}] {{ $u->apellidos }}, {{ $u->nombres }} — {{ $u->nombre_usuario }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Rol *</label>
                    <select name="rol_id" class="form-select" required>
                        <option value="">-- Selecciona --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->rol_id }}">{{ $r->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Asignar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white"><strong>Roles del usuario</strong></div>
        <div class="card-body p-0">
            @if(!($usuarioId))
                <div class="p-3 text-muted">Selecciona un usuario para ver sus roles.</div>
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
                            <tr><td colspan="3" class="text-muted p-3">Este usuario no tiene roles.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
