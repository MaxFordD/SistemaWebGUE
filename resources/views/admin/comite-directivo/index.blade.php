@extends('layouts.app')

@section('title', 'Administrar Comité Directivo')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-comite-directivo.css') }}" />
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Administrar Comité Directivo</h1>
            <p class="text-muted mb-0">Gestiona el directorio del equipo directivo institucional</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalInactivos">
                <i class="bi bi-archive me-2"></i>Ver Inactivos
            </button>
            <a href="{{ route('admin.comite-directivo.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Nuevo Directivo
            </a>
        </div>
    </div>

    @if($directivos->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            No hay directivos registrados. <a href="{{ route('admin.comite-directivo.create') }}" class="alert-link">Crear uno nuevo</a>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="60">Orden</th>
                                <th width="80">Foto</th>
                                <th>Nombre Completo</th>
                                <th>Cargo</th>
                                <th>Grado</th>
                                <th width="100" class="text-center">Estado</th>
                                <th width="150" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($directivos as $directivo)
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $directivo->orden }}</span>
                                </td>
                                <td>
                                    @if($directivo->foto)
                                        <img src="{{ asset('storage/' . $directivo->foto) }}"
                                             alt="{{ $directivo->nombre_completo }}"
                                             class="rounded-circle"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                                             style="width: 50px; height: 50px;">
                                            <i class="bi bi-person text-white fs-4"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $directivo->nombre_completo }}</div>
                                    @if($directivo->biografia)
                                        <small class="text-muted">
                                            {{ Str::limit($directivo->biografia, 60) }}
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $directivo->cargo }}</td>
                                <td>
                                    @if($directivo->grado_cargo)
                                        <span class="badge bg-info">{{ $directivo->grado_cargo }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($directivo->estado == 'A')
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.comite-directivo.edit', $directivo->directivo_id) }}"
                                           class="btn btn-outline-primary"
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-outline-danger"
                                                onclick="confirmarEliminacion({{ $directivo->directivo_id }}, '{{ addslashes($directivo->nombre_completo) }}')"
                                                title="Desactivar">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>

                                    <form id="delete-form-{{ $directivo->directivo_id }}"
                                          action="{{ route('admin.comite-directivo.destroy', $directivo->directivo_id) }}"
                                          method="POST"
                                          class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Total de directivos activos: <strong>{{ $directivos->count() }}</strong>
            </p>
        </div>
    @endif

    {{-- Modal de Directivos Inactivos --}}
    <div class="modal fade" id="modalInactivos" tabindex="-1" aria-labelledby="modalInactivosLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInactivosLabel">
                        <i class="bi bi-archive me-2"></i>Directivos Inactivos
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="loadingInactivos" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                    <div id="contenidoInactivos" style="display: none;">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Estos directivos están desactivados. Puedes restaurarlos para que vuelvan a aparecer en la lista principal.
                        </div>
                        <div id="listaInactivos"></div>
                        <div id="sinInactivos" style="display: none;" class="text-center text-muted py-4">
                            <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
                            No hay directivos inactivos
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function confirmarEliminacion(id, nombre) {
    if (confirm('¿Estás seguro de desactivar a ' + nombre + '?\n\nEsta acción cambiará su estado a inactivo.')) {
        document.getElementById('delete-form-' + id).submit();
    }
}

function confirmarRestauracion(id, nombre) {
    console.log('Intentando restaurar:', id, nombre);
    if (confirm('¿Deseas reactivar a ' + nombre + '?\n\nEsta persona volverá a aparecer en la lista de directivos activos.')) {
        const form = document.getElementById('restore-form-' + id);
        if (form) {
            console.log('Formulario encontrado, enviando...');
            form.submit();
        } else {
            console.error('No se encontró el formulario restore-form-' + id);
            alert('Error: No se pudo encontrar el formulario de restauración');
        }
    }
}

// Cargar directivos inactivos cuando se abre el modal
document.getElementById('modalInactivos').addEventListener('show.bs.modal', function (event) {
    const loadingDiv = document.getElementById('loadingInactivos');
    const contenidoDiv = document.getElementById('contenidoInactivos');
    const listaDiv = document.getElementById('listaInactivos');
    const sinInactivosDiv = document.getElementById('sinInactivos');

    // Mostrar loading
    loadingDiv.style.display = 'block';
    contenidoDiv.style.display = 'none';
});

// Cargar datos cuando el modal se muestra completamente
document.getElementById('modalInactivos').addEventListener('shown.bs.modal', function (event) {
    const loadingDiv = document.getElementById('loadingInactivos');
    const contenidoDiv = document.getElementById('contenidoInactivos');
    const listaDiv = document.getElementById('listaInactivos');
    const sinInactivosDiv = document.getElementById('sinInactivos');

    // Hacer petición AJAX
    fetch('{{ route("admin.comite-directivo.inactivos") }}')
        .then(response => response.json())
        .then(data => {
            loadingDiv.style.display = 'none';
            contenidoDiv.style.display = 'block';

            if (data.success && data.data.length > 0) {
                // Mostrar lista de inactivos
                sinInactivosDiv.style.display = 'none';
                listaDiv.style.display = 'block';

                let html = '<div class="list-group list-group-flush">';
                data.data.forEach(directivo => {
                    const foto = directivo.foto
                        ? `<img src="/storage/${directivo.foto}" alt="${escapeHtml(directivo.nombre_completo)}" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover; flex-shrink: 0;">`
                        : `<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;"><i class="bi bi-person text-white fs-5"></i></div>`;

                    const gradoCargo = directivo.grado_cargo
                        ? `<span class="badge bg-info ms-2">${escapeHtml(directivo.grado_cargo)}</span>`
                        : '';

                    html += `
                        <div class="list-group-item">
                            <div class="row g-3 align-items-center">
                                <div class="col-auto">
                                    ${foto}
                                </div>
                                <div class="col">
                                    <div class="fw-semibold mb-1">${escapeHtml(directivo.nombre_completo)}</div>
                                    <div class="text-muted small">${escapeHtml(directivo.cargo)} ${gradoCargo}</div>
                                </div>
                                <div class="col-12 col-md-auto">
                                    <form action="{{ route('admin.comite-directivo.restore', '') }}/${directivo.directivo_id}"
                                          method="POST"
                                          style="display: inline-block; width: 100%;">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-sm btn-success w-100"
                                                onclick="return confirm('¿Deseas reactivar a ${escapeHtml(directivo.nombre_completo).replace(/'/g, "\\'")}?\\n\\nEsta persona volverá a aparecer en la lista de directivos activos.');">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                listaDiv.innerHTML = html;
            } else {
                // No hay inactivos
                listaDiv.style.display = 'none';
                sinInactivosDiv.style.display = 'block';
            }
        })
        .catch(error => {
            loadingDiv.style.display = 'none';
            contenidoDiv.style.display = 'block';
            listaDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error al cargar los directivos inactivos.</div>';
            console.error('Error:', error);
        });
});
</script>
@endpush
