@extends('layouts.app')

@section('title', 'Administrar Historia y Legado')

@push('styles')
<style>
.hl-thumb { width: 70px; height: 55px; object-fit: cover; border-radius: 6px; }
.tipo-badge-foto  { background: #0d6efd22; color: #0d6efd; }
.tipo-badge-texto { background: #19875422; color: #198754; }
.tipo-badge-video { background: #dc354522; color: #dc3545; }
.tipo-badge { display:inline-block; padding: 3px 10px; border-radius: 20px; font-size:.8rem; font-weight:600; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Historia y Legado</h1>
            <p class="text-muted mb-0">Gestiona fotos, textos e videos históricos del colegio</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregar">
            <i class="bi bi-plus-circle me-2"></i>Agregar elemento
        </button>
    </div>

    @if($items->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>No hay elementos publicados aún.
            <button class="btn btn-sm btn-primary ms-3" data-bs-toggle="modal" data-bs-target="#modalAgregar">Agregar el primero</button>
        </div>
    @else
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="80">Tipo</th>
                            <th width="90">Vista previa</th>
                            <th>Título</th>
                            <th width="90" class="text-center">Estado</th>
                            <th width="120" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items->sortBy('orden') as $item)
                        <tr>
                            <td>
                                @if($item->tipo === 'foto')
                                    <span class="tipo-badge tipo-badge-foto"><i class="bi bi-image me-1"></i>Foto</span>
                                @elseif($item->tipo === 'texto')
                                    <span class="tipo-badge tipo-badge-texto"><i class="bi bi-file-text me-1"></i>Texto</span>
                                @else
                                    <span class="tipo-badge tipo-badge-video"><i class="bi bi-play-circle me-1"></i>Video</span>
                                @endif
                            </td>
                            <td>
                                @if($item->tipo === 'foto' && $item->archivo)
                                    <img src="{{ asset('storage/' . $item->archivo) }}" alt="{{ $item->titulo }}" class="hl-thumb">
                                @elseif($item->tipo === 'video')
                                    <i class="bi bi-play-circle-fill text-danger" style="font-size:2rem"></i>
                                @else
                                    <i class="bi bi-file-text text-success" style="font-size:2rem"></i>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $item->titulo }}</div>
                                @if($item->tipo === 'texto' && $item->contenido)
                                    <small class="text-muted">{{ Str::limit($item->contenido, 70) }}</small>
                                @elseif($item->tipo === 'video' && $item->url_video)
                                    <small class="text-muted">{{ Str::limit($item->url_video, 60) }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->estado === 'A')
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button class="btn btn-sm btn-outline-primary"
                                            title="Editar"
                                            onclick="abrirEditar({{ json_encode($item) }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.historia-legado.destroy', $item->item_id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar «{{ addslashes($item->titulo) }}»?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Eliminar" type="submit">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
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
{{-- ========== MODAL AGREGAR ========== --}}
<div class="modal fade" id="modalAgregar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.historia-legado.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Agregar elemento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                            <select name="tipo" class="form-select" id="tipoAgregar" onchange="toggleCampos('agregar')">
                                <option value="foto">Fotografía</option>
                                <option value="texto">Texto</option>
                                <option value="video">Video</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                            <input type="text" name="titulo" class="form-control" maxlength="200" required>
                        </div>
                    </div>

                    {{-- Campo foto --}}
                    <div id="campoFotoAgregar" class="mt-3">
                        <label class="form-label fw-semibold">Fotografía <span class="text-danger">*</span></label>
                        <input type="file" name="foto" class="form-control" accept="image/jpeg,image/jpg,image/png,image/webp">
                        <small class="text-muted">JPG, PNG o WebP · máx 4 MB</small>
                        <label class="form-label fw-semibold mt-3">Descripción <small class="text-muted fw-normal">(opcional)</small></label>
                        <textarea name="contenido" class="form-control" rows="3" placeholder="Cuéntanos sobre esta foto, año, contexto histórico..."></textarea>
                    </div>

                    {{-- Campo contenido --}}
                    <div id="campoTextoAgregar" class="mt-3" style="display:none">
                        <label class="form-label fw-semibold">Contenido <span class="text-danger">*</span></label>
                        <textarea name="contenido" class="form-control" rows="8" placeholder="Escribe aquí el texto histórico..."></textarea>
                    </div>

                    {{-- Campo video --}}
                    <div id="campoVideoAgregar" class="mt-3" style="display:none">
                        <label class="form-label fw-semibold">Enlace del video <span class="text-danger">*</span></label>
                        <input type="url" name="url_video" class="form-control"
                               placeholder="https://www.youtube.com/watch?v=...  o  https://youtu.be/...">
                        <small class="text-muted">YouTube o Vimeo — se convierte automáticamente.</small>
                        <label class="form-label fw-semibold mt-3">Descripción <small class="text-muted fw-normal">(opcional)</small></label>
                        <textarea name="contenido" class="form-control" rows="3" placeholder="Contexto del video, año, evento..."></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========== MODAL EDITAR ========== --}}
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEditar" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Editar elemento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                            <select name="tipo" class="form-select" id="tipoEditar" onchange="toggleCampos('editar')">
                                <option value="foto">Fotografía</option>
                                <option value="texto">Texto</option>
                                <option value="video">Video</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                            <input type="text" name="titulo" id="editTitulo" class="form-control" maxlength="200" required>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold">Estado</label>
                        <select name="estado" id="editEstado" class="form-select">
                            <option value="A">Activo</option>
                            <option value="I">Inactivo</option>
                        </select>
                    </div>

                    {{-- Foto --}}
                    <div id="campoFotoEditar" class="mt-3">
                        <label class="form-label fw-semibold">Nueva fotografía <small class="text-muted">(dejar vacío para conservar la actual)</small></label>
                        <input type="file" name="foto" class="form-control" accept="image/jpeg,image/jpg,image/png,image/webp">
                        <div id="editFotoActual" class="mt-2"></div>
                        <label class="form-label fw-semibold mt-3">Descripción <small class="text-muted fw-normal">(opcional)</small></label>
                        <textarea name="contenido" id="editContenidoFoto" class="form-control" rows="3" placeholder="Cuéntanos sobre esta foto, año, contexto histórico..."></textarea>
                    </div>

                    {{-- Texto --}}
                    <div id="campoTextoEditar" class="mt-3" style="display:none">
                        <label class="form-label fw-semibold">Contenido <span class="text-danger">*</span></label>
                        <textarea name="contenido" id="editContenido" class="form-control" rows="8"></textarea>
                    </div>

                    {{-- Video --}}
                    <div id="campoVideoEditar" class="mt-3" style="display:none">
                        <label class="form-label fw-semibold">Enlace del video <span class="text-danger">*</span></label>
                        <input type="url" name="url_video" id="editUrlVideo" class="form-control"
                               placeholder="https://www.youtube.com/watch?v=...">
                        <small class="text-muted">YouTube o Vimeo — se convierte automáticamente.</small>
                        <label class="form-label fw-semibold mt-3">Descripción <small class="text-muted fw-normal">(opcional)</small></label>
                        <textarea name="contenido" id="editContenidoVideo" class="form-control" rows="3" placeholder="Contexto del video, año, evento..."></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
function toggleCampos(modo) {
    const tipo = document.getElementById('tipo' + (modo === 'agregar' ? 'Agregar' : 'Editar')).value;
    const prefix = modo === 'agregar' ? 'Agregar' : 'Editar';

    document.getElementById('campoFoto'  + prefix).style.display = tipo === 'foto'  ? '' : 'none';
    document.getElementById('campoTexto' + prefix).style.display = tipo === 'texto' ? '' : 'none';
    document.getElementById('campoVideo' + prefix).style.display = tipo === 'video' ? '' : 'none';
}

function abrirEditar(item) {
    const baseUrl = '{{ url("admin/historia-legado") }}';
    document.getElementById('formEditar').action = baseUrl + '/' + item.item_id;

    document.getElementById('tipoEditar').value        = item.tipo;
    document.getElementById('editTitulo').value        = item.titulo    || '';
    document.getElementById('editEstado').value        = item.estado    || 'A';
    document.getElementById('editContenido').value     = item.contenido || '';
    document.getElementById('editContenidoFoto').value = item.contenido || '';
    document.getElementById('editContenidoVideo').value= item.contenido || '';
    document.getElementById('editUrlVideo').value      = item.url_video || '';

    // Foto actual
    const fotoDiv = document.getElementById('editFotoActual');
    if (item.tipo === 'foto' && item.archivo) {
        fotoDiv.innerHTML = `<img src="/storage/${item.archivo}" style="height:80px;border-radius:6px;" alt="Foto actual"> <small class="text-muted ms-2">Foto actual</small>`;
    } else {
        fotoDiv.innerHTML = '';
    }

    toggleCampos('editar');
    new bootstrap.Modal(document.getElementById('modalEditar')).show();
}

// Init
document.addEventListener('DOMContentLoaded', () => {
    toggleCampos('agregar');
    toggleCampos('editar');
});
</script>
@endpush
