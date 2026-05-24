@extends('layouts.app')

@section('title', 'Registro de Asistencia')

@push('styles')
<style>
.asistencia-row { transition: background .15s; }
.asistencia-row:hover { background: #f8f9fa; }
.estado-btn-group .btn-check:checked + .btn-asistio  { background:#198754; color:#fff; border-color:#198754; }
.estado-btn-group .btn-check:checked + .btn-falta    { background:#dc3545; color:#fff; border-color:#dc3545; }
.estado-btn-group .btn-check:checked + .btn-tardanza { background:#fd7e14; color:#fff; border-color:#fd7e14; }
.obs-input { border: none; border-bottom: 1px solid #dee2e6; border-radius: 0; padding: 2px 4px; font-size:.85rem; background: transparent; }
.obs-input:focus { outline: none; border-bottom-color: #0d6efd; background: transparent; box-shadow: none; }
.sticky-header { position: sticky; top: 0; z-index: 10; }
.contador-badge { font-size: .8rem; min-width: 38px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="mb-4">
        <h1 class="h3 mb-1 fw-bold"><i class="bi bi-calendar-check me-2 text-primary"></i>Registro de Asistencia</h1>
        <p class="text-muted mb-0">Registra la asistencia diaria de alumnos por sección</p>
    </div>

    {{-- Panel de filtros --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.asistencia.index') }}" class="row g-3 align-items-end" id="formFiltro">
                <div class="col-md-2 col-6">
                    <label class="form-label fw-semibold small">Año lectivo</label>
                    <input type="number" class="form-control" name="año" id="inputAño"
                           value="{{ $año }}" min="2020" max="2099">
                </div>
                <div class="col-md-4">
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
                <div class="col-md-3 col-6">
                    <label class="form-label fw-semibold small">Fecha</label>
                    <input type="date" class="form-control" name="fecha" id="inputFecha"
                           value="{{ $fecha }}" max="{{ date('Y-m-d') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>Cargar
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(!$seccionId)
        <div class="alert alert-info d-flex align-items-center gap-2">
            <i class="bi bi-arrow-up-circle fs-4"></i>
            <span>Selecciona el año lectivo, la sección y la fecha para registrar la asistencia.</span>
        </div>
    @elseif($alumnos->isEmpty())
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-circle me-2"></i>
            No hay alumnos activos en esta sección.
            <a href="{{ route('admin.alumnos.index', ['seccion_id' => $seccionId, 'año' => $año]) }}" class="alert-link">
                Registrar alumnos
            </a>
        </div>
    @else
        {{-- Info de la sección --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <span class="fw-bold fs-5">{{ $seccion->grado }} — Sección {{ $seccion->seccion }}</span>
                <span class="badge bg-{{ $seccion->nivel === 'Primaria' ? 'info' : 'warning' }} ms-2">{{ $seccion->nivel }}</span>
                <span class="text-muted ms-2 small">| Turno: {{ $seccion->turno }}</span>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <span class="badge bg-success contador-badge text-center" id="cntAsistio">0 ✓</span>
                <span class="badge bg-danger contador-badge text-center" id="cntFalta">0 ✗</span>
                <span class="badge bg-warning text-dark contador-badge text-center" id="cntTardanza">0 ⏰</span>
            </div>
        </div>

        {{-- Acciones rápidas --}}
        <div class="d-flex gap-2 mb-3 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-success" onclick="marcarTodos('Asistio')">
                <i class="bi bi-check-all me-1"></i>Todos Asistieron
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="marcarTodos('Falta')">
                <i class="bi bi-x-lg me-1"></i>Todos Faltaron
            </button>
            <span class="text-muted small align-self-center ms-2">
                <i class="bi bi-calendar3 me-1"></i>
                {{ \Carbon\Carbon::parse($fecha)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
            </span>
        </div>

        {{-- Formulario de asistencia --}}
        <form method="POST" action="{{ route('admin.asistencia.guardar') }}" id="formAsistencia">
            @csrf
            <input type="hidden" name="seccion_id" value="{{ $seccionId }}">
            <input type="hidden" name="fecha" value="{{ $fecha }}">
            <input type="hidden" name="año" value="{{ $año }}">

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle" id="tablaAsistencia">
                            <thead class="table-dark sticky-header">
                                <tr>
                                    <th width="45" class="text-center">#</th>
                                    <th>Apellidos y Nombres</th>
                                    <th width="80" class="text-center small">DNI</th>
                                    <th width="260" class="text-center">Estado de Asistencia</th>
                                    <th>Observación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($alumnos as $i => $a)
                                <tr class="asistencia-row" data-estado="{{ $a->estado_asistencia }}">
                                    <td class="text-center text-muted small">{{ $i + 1 }}</td>
                                    <td>
                                        <span class="fw-semibold">{{ $a->apellidos }}, {{ $a->nombres }}</span>
                                        @if($a->sexo === 'F')
                                            <i class="bi bi-gender-female ms-1 small" style="color:#e91e8c"></i>
                                        @else
                                            <i class="bi bi-gender-male ms-1 small text-info"></i>
                                        @endif
                                    </td>
                                    <td class="text-center font-monospace small text-muted">{{ $a->dni }}</td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1 estado-btn-group">
                                            {{-- Asistió --}}
                                            <input class="btn-check" type="radio"
                                                name="asistencia[{{ $a->alumno_id }}][estado]"
                                                id="a{{ $a->alumno_id }}_A"
                                                value="Asistio"
                                                {{ $a->estado_asistencia === 'Asistio' ? 'checked' : '' }}
                                                onchange="actualizarContadores()">
                                            <label class="btn btn-sm btn-outline-success btn-asistio px-2" for="a{{ $a->alumno_id }}_A" title="Asistió">
                                                <i class="bi bi-check-lg"></i> Asistió
                                            </label>

                                            {{-- Falta --}}
                                            <input class="btn-check" type="radio"
                                                name="asistencia[{{ $a->alumno_id }}][estado]"
                                                id="a{{ $a->alumno_id }}_F"
                                                value="Falta"
                                                {{ $a->estado_asistencia === 'Falta' ? 'checked' : '' }}
                                                onchange="actualizarContadores()">
                                            <label class="btn btn-sm btn-outline-danger btn-falta px-2" for="a{{ $a->alumno_id }}_F" title="Falta">
                                                <i class="bi bi-x-lg"></i> Falta
                                            </label>

                                            {{-- Tardanza --}}
                                            <input class="btn-check" type="radio"
                                                name="asistencia[{{ $a->alumno_id }}][estado]"
                                                id="a{{ $a->alumno_id }}_T"
                                                value="Tardanza"
                                                {{ $a->estado_asistencia === 'Tardanza' ? 'checked' : '' }}
                                                onchange="actualizarContadores()">
                                            <label class="btn btn-sm btn-outline-warning btn-tardanza px-2" for="a{{ $a->alumno_id }}_T" title="Tardanza">
                                                <i class="bi bi-clock"></i> Tardanza
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text"
                                            class="form-control obs-input w-100"
                                            name="asistencia[{{ $a->alumno_id }}][observacion]"
                                            value="{{ old("asistencia.{$a->alumno_id}.observacion", $a->observacion ?? '') }}"
                                            placeholder="Observación opcional…"
                                            maxlength="255">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                    <span class="text-muted small">
                        <i class="bi bi-people me-1"></i>{{ $alumnos->count() }} alumno(s) en total
                    </span>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-2"></i>Guardar Asistencia
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
function actualizarContadores() {
    let asistio = 0, falta = 0, tardanza = 0;
    document.querySelectorAll('input[type="radio"]:checked').forEach(r => {
        if (r.value === 'Asistio')  asistio++;
        if (r.value === 'Falta')    falta++;
        if (r.value === 'Tardanza') tardanza++;
    });
    document.getElementById('cntAsistio').textContent  = asistio  + ' ✓';
    document.getElementById('cntFalta').textContent    = falta    + ' ✗';
    document.getElementById('cntTardanza').textContent = tardanza + ' ⏰';
}

function marcarTodos(estado) {
    document.querySelectorAll(`input[type="radio"][value="${estado}"]`).forEach(r => r.checked = true);
    actualizarContadores();
}

// Confirmación antes de guardar
document.getElementById('formAsistencia')?.addEventListener('submit', function(e) {
    const total = document.querySelectorAll('input[type="radio"]').length / 3;
    const marcados = document.querySelectorAll('input[type="radio"]:checked').length;
    if (marcados < total) {
        if (!confirm(`Hay ${total - marcados} alumno(s) sin marcar. ¿Guardar de todas formas?`)) {
            e.preventDefault();
        }
    }
});

// Contar al cargar la página
document.addEventListener('DOMContentLoaded', actualizarContadores);

// Envío del filtro al cambiar sección o fecha
document.getElementById('selectSeccion')?.addEventListener('change', () => document.getElementById('formFiltro').submit());
document.getElementById('inputFecha')?.addEventListener('change', function() {
    if (document.getElementById('selectSeccion').value) {
        document.getElementById('formFiltro').submit();
    }
});
</script>
@endpush
