@extends('layouts.app')

@section('title', 'Gestionar Alumnos')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Gestionar Alumnos</h1>
            <p class="text-muted mb-0">
                @if($seccion)
                    {{ $seccion->grado }} {{ $seccion->seccion }} — {{ $seccion->nivel }} | Turno: {{ $seccion->turno }}
                @else
                    Selecciona una sección para ver o registrar alumnos
                @endif
            </p>
        </div>
        @if($seccion)
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAlumno" onclick="modoCrear()">
            <i class="bi bi-person-plus me-2"></i>Nuevo Alumno
        </button>
        @endif
    </div>

    {{-- Filtros: año + sección --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.alumnos.index') }}" class="row g-3 align-items-end">
                <div class="col-md-2 col-6">
                    <label class="form-label fw-semibold small">Año lectivo</label>
                    <input type="number" class="form-control" name="año"
                           value="{{ $año }}" min="2020" max="2099">
                </div>
                <div class="col-md-4 col-6">
                    <label class="form-label fw-semibold small">Sección</label>
                    <select class="form-select" name="seccion_id" id="selectSeccion">
                        <option value="">— Seleccionar sección —</option>
                        @foreach($secciones->groupBy('nivel') as $nivel => $lista)
                            <optgroup label="{{ $nivel }}">
                                @foreach($lista as $s)
                                    <option value="{{ $s->seccion_id }}"
                                        {{ $seccionId == $s->seccion_id ? 'selected' : '' }}>
                                        {{ $s->grado }} — Sección {{ $s->seccion }} ({{ $s->turno }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>Ver alumnos
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de alumnos --}}
    @if(!$seccionId)
        <div class="alert alert-info">
            <i class="bi bi-arrow-up-circle me-2"></i>
            Selecciona el año lectivo y la sección para listar los alumnos.
        </div>
    @elseif($alumnos->isEmpty())
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-circle me-2"></i>
            No hay alumnos registrados en esta sección.
            <button type="button" class="btn btn-sm btn-primary ms-3"
                    data-bs-toggle="modal" data-bs-target="#modalAlumno" onclick="modoCrear()">
                <i class="bi bi-person-plus me-1"></i>Registrar primer alumno
            </button>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <span class="fw-semibold">
                    <i class="bi bi-people me-2 text-primary"></i>
                    {{ $alumnos->count() }} alumno(s) registrado(s)
                </span>
                <div class="d-flex gap-2">
                    <span class="badge bg-success">{{ $alumnos->where('estado', 1)->count() }} activos</span>
                    <span class="badge bg-secondary">{{ $alumnos->where('estado', 0)->count() }} inactivos</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="50" class="text-center">#</th>
                                <th>Apellidos y Nombres</th>
                                <th width="110">DNI</th>
                                <th width="90" class="text-center">Sexo</th>
                                <th width="130">F. Nacimiento</th>
                                <th width="100" class="text-center">Estado</th>
                                <th width="120" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alumnos as $i => $a)
                            <tr class="{{ $a->estado ? '' : 'table-secondary text-muted' }}">
                                <td class="text-center text-muted small">{{ $i + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $a->apellidos }}, {{ $a->nombres }}</div>
                                </td>
                                <td class="font-monospace">{{ $a->dni }}</td>
                                <td class="text-center">
                                    @if($a->sexo === 'M')
                                        <span class="badge bg-info"><i class="bi bi-gender-male me-1"></i>M</span>
                                    @else
                                        <span class="badge bg-pink" style="background:#e91e8c!important"><i class="bi bi-gender-female me-1"></i>F</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $a->fecha_nacimiento ? \Carbon\Carbon::parse($a->fecha_nacimiento)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="text-center">
                                    @if($a->estado)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" title="Editar"
                                            onclick="modoEditar(
                                                {{ $a->alumno_id }},
                                                {{ $a->seccion_id }},
                                                '{{ addslashes($a->nombres) }}',
                                                '{{ addslashes($a->apellidos) }}',
                                                '{{ $a->dni }}',
                                                '{{ $a->fecha_nacimiento ?? '' }}',
                                                '{{ $a->sexo }}',
                                                {{ $a->estado }}
                                            )">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" title="Desactivar"
                                            onclick="confirmarEliminar({{ $a->alumno_id }}, '{{ addslashes($a->apellidos) }}, {{ addslashes($a->nombres) }}')">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                    <form id="del-{{ $a->alumno_id }}"
                                          action="{{ route('admin.alumnos.destroy', $a->alumno_id) }}"
                                          method="POST" class="d-none">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="seccion_id" value="{{ $seccionId }}">
                                        <input type="hidden" name="año" value="{{ $año }}">
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

@endsection

@push('modals')
{{-- Modal Crear / Editar Alumno --}}
<div class="modal fade" id="modalAlumno" tabindex="-1" aria-labelledby="modalAlumnoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formAlumno" method="POST" action="{{ route('admin.alumnos.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="seccion_id" id="hSeccionId" value="{{ $seccionId }}">
                <input type="hidden" name="año" value="{{ $año }}">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalAlumnoLabel">
                        <i class="bi bi-person-plus me-2"></i><span id="tituloModal">Nuevo Alumno</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="apellidos" class="form-label fw-semibold">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" id="apellidos" name="apellidos"
                                   placeholder="APELLIDO PATERNO MATERNO" maxlength="100" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nombres" class="form-label fw-semibold">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" id="nombres" name="nombres"
                                   placeholder="NOMBRE(S)" maxlength="100" required>
                        </div>
                        <div class="col-md-4">
                            <label for="dni" class="form-label fw-semibold">DNI <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-monospace" id="dni" name="dni"
                                   placeholder="12345678" maxlength="8" pattern="[0-9]{8}" required>
                            <div class="form-text">8 dígitos numéricos</div>
                        </div>
                        <div class="col-md-4">
                            <label for="fecha_nacimiento" class="form-label fw-semibold">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Sexo <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="sexo" id="sexoM" value="M" required>
                                    <label class="form-check-label" for="sexoM">
                                        <i class="bi bi-gender-male text-info me-1"></i>Masculino
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="sexo" id="sexoF" value="F">
                                    <label class="form-check-label" for="sexoF">
                                        <i class="bi bi-gender-female me-1" style="color:#e91e8c"></i>Femenino
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 d-none" id="campoEstado">
                            <label for="estado" class="form-label fw-semibold">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
const form     = document.getElementById('formAlumno');
const storeUrl = '{{ route("admin.alumnos.store") }}';
const baseEditUrl = '{{ url("admin/alumnos") }}';

function modoCrear() {
    document.getElementById('tituloModal').textContent = 'Nuevo Alumno';
    form.action = storeUrl;
    document.getElementById('formMethod').value = 'POST';
    form.apellidos.value = '';
    form.nombres.value   = '';
    form.dni.value       = '';
    form.fecha_nacimiento.value = '';
    document.querySelectorAll('input[name="sexo"]').forEach(r => r.checked = false);
    document.getElementById('campoEstado').classList.add('d-none');
}

function modoEditar(id, seccionId, nombres, apellidos, dni, fechaNac, sexo, estado) {
    document.getElementById('tituloModal').textContent = 'Editar Alumno';
    form.action = `${baseEditUrl}/${id}`;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('hSeccionId').value = seccionId;
    form.apellidos.value = apellidos;
    form.nombres.value   = nombres;
    form.dni.value       = dni;
    form.fecha_nacimiento.value = fechaNac;
    document.querySelector(`input[name="sexo"][value="${sexo}"]`).checked = true;
    form.estado.value = estado;
    document.getElementById('campoEstado').classList.remove('d-none');
    new bootstrap.Modal(document.getElementById('modalAlumno')).show();
}

function confirmarEliminar(id, nombre) {
    if (confirm(`¿Desactivar al alumno "${nombre}"?`)) {
        document.getElementById('del-' + id).submit();
    }
}

// Forzar mayúsculas en nombres y apellidos
['nombres','apellidos'].forEach(function(campo) {
    const el = document.getElementById(campo);
    if (el) el.addEventListener('input', () => el.value = el.value.toUpperCase());
});

// Validar que DNI solo tenga números
document.getElementById('dni').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 8);
});
</script>
@endpush
