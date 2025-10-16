@extends('layouts.app')

@section('title', 'Gestionar Roles del Usuario')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Gestionar Roles del Usuario</h1>
        <a href="{{ route('admin.usuario-rol.index') }}" class="btn btn-secondary">Volver</a>
    </div>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <div class="card mb-4">
        <div class="card-header bg-white"><strong>Información del Usuario</strong></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Usuario:</strong> {{ $usuario->nombre_usuario }}</p>
                    <p><strong>Persona:</strong> {{ $usuario->apellidos }}, {{ $usuario->nombres }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>DNI:</strong> {{ $usuario->dni }}</p>
                    <p><strong>Correo:</strong> {{ $usuario->correo }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-white"><strong>Roles Asignados</strong></div>
                <div class="card-body">
                    @if($rolesAsignados->isEmpty())
                        <p class="text-muted">Este usuario no tiene roles asignados.</p>
                    @else
                        <div class="list-group">
                            @foreach($rolesAsignados as $rol)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $rol->nombre }}</strong>
                                        @if($rol->descripcion)
                                            <br><small class="text-muted">{{ $rol->descripcion }}</small>
                                        @endif
                                    </div>
                                    <form action="{{ route('admin.usuario-rol.remover') }}" method="POST" onsubmit="return confirm('¿Remover este rol?');">
                                        @csrf
                                        <input type="hidden" name="usuario_id" value="{{ $usuario->usuario_id }}">
                                        <input type="hidden" name="rol_id" value="{{ $rol->rol_id }}">
                                        <button class="btn btn-sm btn-outline-danger">Remover</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white"><strong>Roles Disponibles</strong></div>
                <div class="card-body">
                    @if($rolesDisponibles->isEmpty())
                        <p class="text-muted">No hay roles disponibles para asignar.</p>
                    @else
                        <div class="list-group">
                            @foreach($rolesDisponibles as $rol)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $rol->nombre }}</strong>
                                        @if($rol->descripcion)
                                            <br><small class="text-muted">{{ $rol->descripcion }}</small>
                                        @endif
                                    </div>
                                    <form action="{{ route('admin.usuario-rol.asignar') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="usuario_id" value="{{ $usuario->usuario_id }}">
                                        <input type="hidden" name="rol_id" value="{{ $rol->rol_id }}">
                                        <button class="btn btn-sm btn-outline-success">Asignar</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
