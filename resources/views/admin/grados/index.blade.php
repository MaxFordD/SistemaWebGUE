@extends('layouts.app')

@section('title', 'Gestionar Grados')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Gestionar Grados</h1>
            <p class="text-muted mb-0">Registra y administra los grados de la institución</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGrado">
            <i class="bi bi-plus-circle me-2"></i>Nuevo Grado
        </button>
    </div>

    @if($grados->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            No hay grados registrados. Crea el primero haciendo clic en <strong>Nuevo Grado</strong>.
        </div>
    @else
        {{-- Primaria --}}
        @foreach(['Primaria', 'Secundaria'] as $nivel)
            @php $porNivel = $grados->where('nivel', $nivel); @endphp
            @if($porNivel->count())
            <div class="mb-4">
                <h5 class="fw-semibold mb-3">
                    <span class="badge bg-{{ $nivel === 'Primaria' ? 'info' : 'warning' }} me-2">{{ $nivel }}</span>
                </h5>
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Nivel</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center" width="130">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($porNivel as $grado)
                                    <tr>
                                        <td class="fw-semibold">{{ $grado->nombre }}</td>
                                        <td>
                                            <span class="badge bg-{{ $grado->nivel === 'Primaria' ? 'info' : 'warning' }}">
                                                {{ $grado->nivel }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($grado->estado)
                                                <span class="badge bg-success">Activo</span>
                                            @else
                                                <span class="badge bg-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button"
                                                    class="btn btn-outline-primary"
                                                    title="Editar"
                                                    onclick="abrirEditar({{ $grado->grado_id }}, '{{ addslashes($grado->nombre) }}', '{{ $grado->nivel }}', {{ $grado->estado }})">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button"
                                                    class="btn btn-outline-danger"
                                                    title="Desactivar"
                                                    onclick="confirmarEliminar({{ $grado->grado_id }}, '{{ addslashes($grado->nombre) }}')">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </div>
                                            <form id="del-{{ $grado->grado_id }}"
                                                  action="{{ route('admin.grados.destroy', $grado->grado_id) }}"
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
            </div>
            @endif
        @endforeach

        <p class="text-muted small">
            <i class="bi bi-info-circle me-1"></i>Total: <strong>{{ $grados->count() }}</strong> grado(s) registrado(s).
        </p>
    @endif
</div>

@endsection

@push('modals')
{{-- Modal Crear / Editar --}}
<div class="modal fade" id="modalGrado" tabindex="-1" aria-labelledby="modalGradoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formGrado" method="POST" action="{{ route('admin.grados.store') }}">
                @csrf
                <span id="methodField"></span>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalGradoLabel">
                        <i class="bi bi-journal-plus me-2"></i><span id="tituloModal">Nuevo Grado</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-semibold">Nombre del Grado <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre"
                               placeholder="Ej: 1ro, 2do, 3ro…" maxlength="30" required>
                    </div>
                    <div class="mb-3">
                        <label for="nivel" class="form-label fw-semibold">Nivel <span class="text-danger">*</span></label>
                        <select class="form-select" id="nivel" name="nivel" required>
                            <option value="">Seleccionar…</option>
                            <option value="Primaria">Primaria</option>
                            <option value="Secundaria">Secundaria</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="campoEstado">
                        <label for="estado" class="form-label fw-semibold">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
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
const modalEl = document.getElementById('modalGrado');
const form    = document.getElementById('formGrado');
const baseUrl = '{{ route("admin.grados.store") }}';

// Reset al abrir para "Nuevo"
modalEl.addEventListener('show.bs.modal', function(e) {
    if (e.relatedTarget && e.relatedTarget.dataset.bsToggle === 'modal') {
        resetModal();
    }
});

function resetModal() {
    document.getElementById('tituloModal').textContent = 'Nuevo Grado';
    form.action = baseUrl;
    document.getElementById('methodField').innerHTML = '';
    form.nombre.value = '';
    form.nivel.value  = '';
    document.getElementById('campoEstado').classList.add('d-none');
}

function abrirEditar(id, nombre, nivel, estado) {
    document.getElementById('tituloModal').textContent = 'Editar Grado';
    form.action = `{{ url('admin/grados') }}/${id}`;
    document.getElementById('methodField').innerHTML =
        '<input type="hidden" name="_method" value="PUT">';

    form.nombre.value = nombre;
    form.nivel.value  = nivel;
    form.estado.value = estado;
    document.getElementById('campoEstado').classList.remove('d-none');

    new bootstrap.Modal(modalEl).show();
}

function confirmarEliminar(id, nombre) {
    if (confirm(`¿Desactivar el grado "${nombre}"?\n\nPuedes reactivarlo editándolo.`)) {
        document.getElementById('del-' + id).submit();
    }
}
</script>
@endpush
