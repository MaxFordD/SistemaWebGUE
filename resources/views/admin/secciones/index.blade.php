@extends('layouts.app')

@section('title', 'Gestionar Secciones')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Gestionar Secciones</h1>
            <p class="text-muted mb-0">Secciones del año lectivo <strong>{{ $año }}</strong></p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            {{-- Filtro de año --}}
            <form method="GET" action="{{ route('admin.secciones.index') }}" class="d-flex gap-2">
                <input type="number" class="form-control form-control-sm" name="año"
                       value="{{ $año }}" min="2020" max="2099" style="width:90px">
                <button class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-funnel"></i>
                </button>
            </form>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSeccion">
                <i class="bi bi-plus-circle me-2"></i>Nueva Sección
            </button>
        </div>
    </div>

    @if($secciones->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            No hay secciones para el año <strong>{{ $año }}</strong>. Crea la primera haciendo clic en <strong>Nueva Sección</strong>.
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Grado</th>
                                <th>Nivel</th>
                                <th>Sección</th>
                                <th>Turno</th>
                                <th>Año Lectivo</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center" width="130">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($secciones as $sec)
                            <tr>
                                <td class="fw-semibold">{{ $sec->grado }}</td>
                                <td>
                                    <span class="badge bg-{{ $sec->nivel === 'Primaria' ? 'info' : 'warning' }}">
                                        {{ $sec->nivel }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary fs-6">{{ $sec->seccion }}</span>
                                </td>
                                <td>
                                    <i class="bi bi-clock me-1 text-muted"></i>{{ $sec->turno }}
                                </td>
                                <td>{{ $sec->año_lectivo }}</td>
                                <td class="text-center">
                                    @if($sec->estado)
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Inactiva</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button"
                                            class="btn btn-outline-primary"
                                            title="Editar"
                                            onclick="abrirEditar({{ $sec->seccion_id }}, {{ $sec->grado_id }}, '{{ addslashes($sec->seccion) }}', '{{ $sec->turno }}', {{ $sec->año_lectivo }}, {{ $sec->estado }})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button"
                                            class="btn btn-outline-danger"
                                            title="Desactivar"
                                            onclick="confirmarEliminar({{ $sec->seccion_id }}, '{{ addslashes($sec->grado) }} - {{ addslashes($sec->seccion) }}')">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                    <form id="del-{{ $sec->seccion_id }}"
                                          action="{{ route('admin.secciones.destroy', $sec->seccion_id) }}"
                                          method="POST" class="d-none">
                                        @csrf @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <p class="text-muted small mt-3">
            <i class="bi bi-info-circle me-1"></i>Total: <strong>{{ $secciones->count() }}</strong> sección(es) en {{ $año }}.
        </p>
    @endif
</div>

@endsection

@push('modals')
{{-- Modal Crear / Editar --}}
<div class="modal fade" id="modalSeccion" tabindex="-1" aria-labelledby="modalSeccionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formSeccion" method="POST" action="{{ route('admin.secciones.store') }}">
                @csrf
                <span id="methodField"></span>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalSeccionLabel">
                        <i class="bi bi-diagram-3 me-2"></i><span id="tituloModal">Nueva Sección</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="grado_id" class="form-label fw-semibold">Grado <span class="text-danger">*</span></label>
                        <select class="form-select" id="grado_id" name="grado_id" required>
                            <option value="">Seleccionar grado…</option>
                            @foreach($grados as $g)
                                <option value="{{ $g->grado_id }}">{{ $g->nombre }} — {{ $g->nivel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label for="nombre" class="form-label fw-semibold">Sección <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" id="nombre" name="nombre"
                                   placeholder="A, B, C…" maxlength="10" required>
                        </div>
                        <div class="col-6">
                            <label for="turno" class="form-label fw-semibold">Turno <span class="text-danger">*</span></label>
                            <select class="form-select" id="turno" name="turno" required>
                                <option value="Mañana">Mañana</option>
                                <option value="Tarde">Tarde</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-6">
                            <label for="año_lectivo" class="form-label fw-semibold">Año Lectivo <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="año_lectivo" name="año_lectivo"
                                   value="{{ $año }}" min="2020" max="2099" required>
                        </div>
                        <div class="col-6 d-none" id="campoEstado">
                            <label for="estado" class="form-label fw-semibold">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="1">Activa</option>
                                <option value="0">Inactiva</option>
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
const modalEl  = document.getElementById('modalSeccion');
const form     = document.getElementById('formSeccion');
const storeUrl = '{{ route("admin.secciones.store") }}';

modalEl.addEventListener('show.bs.modal', function(e) {
    if (e.relatedTarget) resetModal();
});

function resetModal() {
    document.getElementById('tituloModal').textContent = 'Nueva Sección';
    form.action = storeUrl;
    document.getElementById('methodField').innerHTML = '';
    form.grado_id.value    = '';
    form.nombre.value      = '';
    form.turno.value       = 'Mañana';
    form.año_lectivo.value = '{{ $año }}';
    document.getElementById('campoEstado').classList.add('d-none');
}

function abrirEditar(id, gradoId, nombre, turno, año, estado) {
    document.getElementById('tituloModal').textContent = 'Editar Sección';
    form.action = `{{ url('admin/secciones') }}/${id}`;
    document.getElementById('methodField').innerHTML =
        '<input type="hidden" name="_method" value="PUT">';

    form.grado_id.value    = gradoId;
    form.nombre.value      = nombre;
    form.turno.value       = turno;
    form.año_lectivo.value = año;
    form.estado.value      = estado;
    document.getElementById('campoEstado').classList.remove('d-none');

    new bootstrap.Modal(modalEl).show();
}

function confirmarEliminar(id, nombre) {
    if (confirm(`¿Desactivar la sección "${nombre}"?`)) {
        document.getElementById('del-' + id).submit();
    }
}
</script>
@endpush
