@extends('layouts.app')

@section('title', 'Gestión de Permisos por Rol')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-2">
        <h1 class="h4 fw-bold mb-0 text-primary">
            <i class="bi bi-shield-lock me-2"></i>Permisos por Rol
        </h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    {{-- Tabs de roles --}}
    <ul class="nav nav-tabs mb-0" id="rolesTabs" role="tablist">
        @foreach($roles as $i => $rol)
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $i === 0 ? 'active' : '' }}"
                    id="tab-rol-{{ $rol->rol_id }}"
                    data-bs-toggle="tab"
                    data-bs-target="#panel-rol-{{ $rol->rol_id }}"
                    type="button" role="tab">
                <i class="bi bi-person-badge me-1"></i>{{ $rol->nombre }}
            </button>
        </li>
        @endforeach
    </ul>

    <div class="tab-content border border-top-0 rounded-bottom bg-white p-4 shadow-sm">
        @foreach($roles as $i => $rol)
        <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}"
             id="panel-rol-{{ $rol->rol_id }}"
             role="tabpanel">

            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="mb-0 fw-bold">{{ $rol->nombre }}</h5>
                    @if($rol->descripcion)
                        <small class="text-muted">{{ $rol->descripcion }}</small>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-success"
                            onclick="marcarTodos({{ $rol->rol_id }}, true)">
                        <i class="bi bi-check-all me-1"></i>Marcar todo
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            onclick="marcarTodos({{ $rol->rol_id }}, false)">
                        <i class="bi bi-x-lg me-1"></i>Desmarcar todo
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.roles-permisos.update', $rol->rol_id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    @foreach($permisos as $modulo => $permisosModulo)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 bg-light">
                            <div class="card-header border-0 py-2" style="background-color:#7B1C3A;">
                                <h6 class="mb-0 fw-semibold text-white">
                                    <i class="bi bi-grid-3x3-gap me-1"></i>{{ $modulo }}
                                </h6>
                            </div>
                            <div class="card-body py-2">
                                @foreach($permisosModulo as $permiso)
                                <div class="form-check mb-1">
                                    <input class="form-check-input permiso-check-{{ $rol->rol_id }}"
                                           type="checkbox"
                                           name="permisos[]"
                                           value="{{ $permiso->permiso_id }}"
                                           id="perm_{{ $rol->rol_id }}_{{ $permiso->permiso_id }}"
                                           {{ in_array($permiso->permiso_id, $rolPermisos[$rol->rol_id] ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="perm_{{ $rol->rol_id }}_{{ $permiso->permiso_id }}">
                                        <span class="fw-semibold">{{ $permiso->nombre }}</span>
                                        @if($permiso->descripcion)
                                            <br><span class="text-muted" style="font-size:.75rem;">{{ $permiso->descripcion }}</span>
                                        @endif
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-floppy me-1"></i>Guardar permisos de "{{ $rol->nombre }}"
                    </button>
                </div>
            </form>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
function marcarTodos(rolId, estado) {
    document.querySelectorAll('.permiso-check-' + rolId).forEach(cb => cb.checked = estado);
}
</script>
@endpush
@endsection
