@extends('layouts.app')

@section('title', 'Imágenes del Inicio')

@push('styles')
<style>
.img-thumb {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: .5rem .5rem 0 0;
}
.img-card { border: 1px solid #dee2e6; border-radius: .5rem; overflow: hidden; background: #fff; }
.img-card .card-body { padding: .75rem 1rem; }
.img-preview-modal { max-height: 200px; width: 100%; object-fit: contain; background: #f8f9fa; border-radius: .375rem; }
.card-add {
    border: 2px dashed #adb5bd;
    border-radius: .5rem;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 240px;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    text-decoration: none;
    color: #6c757d;
}
.card-add:hover { border-color: #0d6efd; background: #e9f2ff; color: #0d6efd; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

  {{-- Alertas --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="h3 fw-bold mb-0">Imágenes del Inicio</h1>
      <small class="text-muted">Carrusel principal y tarjetas de talleres</small>
    </div>
    <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-eye me-1"></i>Ver página de inicio
    </a>
  </div>

  {{-- ====== CARRUSEL ====== --}}
  <div class="d-flex align-items-center gap-3 mb-3">
    <h5 class="fw-semibold mb-0">
      <i class="bi bi-images me-2 text-primary"></i>Carrusel Principal
      <span class="badge bg-primary ms-1">{{ $carousel->count() }}</span>
    </h5>
    <button class="btn btn-primary btn-sm"
            data-bs-toggle="modal" data-bs-target="#modalAgregar"
            data-seccion="carousel">
      <i class="bi bi-plus-lg me-1"></i>Nueva Imagen
    </button>
  </div>

  <div class="row g-3 mb-5">
    @foreach($carousel as $img)
    <div class="col-6 col-md-4 col-lg-3">
      <div class="img-card shadow-sm">
        <img src="{{ asset($img->ruta) }}" alt="{{ $img->alt }}" class="img-thumb"
             onerror="this.src='https://placehold.co/400x200?text=Sin+imagen'">
        <div class="card-body">
          <span class="badge bg-primary mb-1" style="font-size:.7rem">Diapositiva {{ $img->orden }}</span>
          <p class="small text-muted mb-2 text-truncate" title="{{ $img->alt }}">{{ $img->alt }}</p>
          <div class="d-flex gap-1">
            <button class="btn btn-warning btn-sm flex-grow-1"
                    data-bs-toggle="modal" data-bs-target="#modalEditar"
                    data-id="{{ $img->id }}"
                    data-seccion="carousel"
                    data-alt="{{ $img->alt }}"
                    data-src="{{ asset($img->ruta) }}">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-danger btn-sm"
                    data-bs-toggle="modal" data-bs-target="#modalEliminar"
                    data-id="{{ $img->id }}"
                    data-nombre="Diapositiva {{ $img->orden }} — {{ $img->alt }}">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>{{-- fin row carrusel --}}

  {{-- ====== TALLERES ====== --}}
  <div class="d-flex align-items-center gap-3 mb-3">
    <h5 class="fw-semibold mb-0">
      <i class="bi bi-grid me-2 text-success"></i>Tarjetas de Talleres
      <span class="badge bg-success ms-1">{{ $talleres->count() }}</span>
    </h5>
    <button class="btn btn-success btn-sm"
            data-bs-toggle="modal" data-bs-target="#modalAgregar"
            data-seccion="taller">
      <i class="bi bi-plus-lg me-1"></i>Nuevo taller
    </button>
  </div>

  <div class="row g-3">
    @foreach($talleres as $img)
    <div class="col-6 col-md-4 col-lg-3">
      <div class="img-card shadow-sm">
        <img src="{{ asset($img->ruta) }}" alt="{{ $img->alt }}" class="img-thumb"
             onerror="this.src='https://placehold.co/400x200?text=Sin+imagen'">
        <div class="card-body">
          <span class="badge bg-success mb-1" style="font-size:.7rem">
            <i class="bi bi-{{ $img->icono }} me-1"></i>{{ $img->titulo }}
          </span>
          <p class="small text-muted mb-2 text-truncate" title="{{ $img->descripcion }}">{{ $img->descripcion }}</p>
          <div class="d-flex gap-1">
            <button class="btn btn-warning btn-sm flex-grow-1"
                    data-bs-toggle="modal" data-bs-target="#modalEditar"
                    data-id="{{ $img->id }}"
                    data-seccion="taller"
                    data-alt="{{ $img->alt }}"
                    data-titulo="{{ $img->titulo }}"
                    data-descripcion="{{ $img->descripcion }}"
                    data-icono="{{ $img->icono }}"
                    data-src="{{ asset($img->ruta) }}">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-danger btn-sm"
                    data-bs-toggle="modal" data-bs-target="#modalEliminar"
                    data-id="{{ $img->id }}"
                    data-nombre="{{ $img->titulo }}">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>{{-- fin row talleres --}}

</div>{{-- fin container-fluid --}}
@endsection

@push('modals')

{{-- ====== MODAL EDITAR ====== --}}
<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="tituloModalEditar" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tituloModalEditar">Editar imagen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formEditar" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="text-center mb-3">
            <img id="editPreview" src="" alt="Vista previa" class="img-preview-modal border">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nueva imagen <span class="text-muted fw-normal">(JPG, PNG, WEBP · máx. 2 MB)</span></label>
            <input type="file" name="foto" id="editFoto" class="form-control" accept="image/jpeg,image/png,image/webp">
            <small class="text-muted">Dejar vacío para conservar la imagen actual.</small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Texto alternativo <span class="text-danger">*</span></label>
            <input type="text" name="alt" id="editAlt" class="form-control" maxlength="255" required>
          </div>
          <div id="editCamposTaller">
            <div class="mb-3">
              <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
              <input type="text" name="titulo" id="editTitulo" class="form-control" maxlength="100">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Descripción <span class="text-danger">*</span></label>
              <input type="text" name="descripcion" id="editDescripcion" class="form-control" maxlength="255">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Ícono Bootstrap Icons <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i id="editIconPreview" class="bi"></i></span>
                <input type="text" name="icono" id="editIcono" class="form-control" maxlength="50" placeholder="ej: music-note-beamed">
              </div>
              <small class="text-muted">Sin "bi-". <a href="https://icons.getbootstrap.com/" target="_blank">Ver íconos</a></small>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ====== MODAL AGREGAR ====== --}}
<div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="tituloModalAgregar" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tituloModalAgregar">Nueva imagen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formAgregar" method="POST" action="{{ route('admin.imagenes-inicio.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="seccion" id="agregarSeccion" value="">
        <div class="modal-body">
          <div class="text-center mb-3" id="agregarPreviewWrap" style="display:none">
            <img id="agregarPreview" src="" alt="Vista previa" class="img-preview-modal border">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Imagen <span class="text-danger">*</span> <span class="text-muted fw-normal">(JPG, PNG, WEBP · máx. 2 MB)</span></label>
            <input type="file" name="foto" id="agregarFoto" class="form-control" accept="image/jpeg,image/png,image/webp" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Texto alternativo <span class="text-danger">*</span></label>
            <input type="text" name="alt" id="agregarAlt" class="form-control" maxlength="255" required>
          </div>
          <div id="agregarCamposTaller">
            <div class="mb-3">
              <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
              <input type="text" name="titulo" id="agregarTitulo" class="form-control" maxlength="100">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Descripción <span class="text-danger">*</span></label>
              <input type="text" name="descripcion" id="agregarDescripcion" class="form-control" maxlength="255">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Ícono Bootstrap Icons <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i id="agregarIconPreview" class="bi bi-star"></i></span>
                <input type="text" name="icono" id="agregarIcono" class="form-control" maxlength="50" placeholder="ej: trophy">
              </div>
              <small class="text-muted">Sin "bi-". <a href="https://icons.getbootstrap.com/" target="_blank">Ver íconos</a></small>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Agregar</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ====== MODAL ELIMINAR ====== --}}
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="tituloModalEliminar" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title text-danger" id="tituloModalEliminar">
          <i class="bi bi-exclamation-triangle me-2"></i>Eliminar imagen
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center py-3">
        <p class="mb-1">¿Eliminar esta imagen?</p>
        <p class="fw-semibold small text-muted" id="eliminarNombre"></p>
        <small class="text-danger">Esta acción no se puede deshacer.</small>
      </div>
      <form id="formEliminar" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-footer border-0 pt-0 justify-content-center">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger btn-sm">
            <i class="bi bi-trash me-1"></i>Sí, eliminar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endpush

@push('scripts')
<script>
(function () {

  // ---- MODAL EDITAR ----
  const modalEditar = document.getElementById('modalEditar');
  modalEditar.addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const id      = btn.dataset.id;
    const seccion = btn.dataset.seccion;

    document.getElementById('formEditar').action = `/admin/imagenes-inicio/${id}`;
    document.getElementById('editPreview').src    = btn.dataset.src;
    document.getElementById('editAlt').value      = btn.dataset.alt;
    document.getElementById('editFoto').value     = '';

    const campos = document.getElementById('editCamposTaller');
    if (seccion === 'taller') {
      campos.style.display = '';
      document.getElementById('editTitulo').value        = btn.dataset.titulo      || '';
      document.getElementById('editDescripcion').value   = btn.dataset.descripcion || '';
      document.getElementById('editIcono').value         = btn.dataset.icono       || '';
      document.getElementById('editIconPreview').className = 'bi bi-' + (btn.dataset.icono || 'star');
      ['editTitulo','editDescripcion','editIcono'].forEach(id => document.getElementById(id).required = true);
    } else {
      campos.style.display = 'none';
      ['editTitulo','editDescripcion','editIcono'].forEach(id => document.getElementById(id).required = false);
    }
  });

  document.getElementById('editFoto').addEventListener('change', function () {
    if (this.files[0]) document.getElementById('editPreview').src = URL.createObjectURL(this.files[0]);
  });

  document.getElementById('editIcono').addEventListener('input', function () {
    document.getElementById('editIconPreview').className = 'bi bi-' + this.value.trim();
  });

  // ---- MODAL AGREGAR ----
  const modalAgregar = document.getElementById('modalAgregar');
  modalAgregar.addEventListener('show.bs.modal', function (e) {
    const seccion = e.relatedTarget.dataset.seccion;
    document.getElementById('agregarSeccion').value = seccion;

    const esTaller = seccion === 'taller';
    document.getElementById('tituloModalAgregar').textContent =
      esTaller ? 'Nuevo taller' : 'Nueva diapositiva';

    document.getElementById('agregarCamposTaller').style.display = esTaller ? '' : 'none';
    ['agregarTitulo','agregarDescripcion','agregarIcono'].forEach(id => {
      document.getElementById(id).required = esTaller;
      document.getElementById(id).value    = '';
    });

    // Limpiar
    document.getElementById('agregarFoto').value = '';
    document.getElementById('agregarAlt').value  = '';
    document.getElementById('agregarPreviewWrap').style.display = 'none';
    document.getElementById('agregarIconPreview').className = 'bi bi-star';
  });

  document.getElementById('agregarFoto').addEventListener('change', function () {
    if (this.files[0]) {
      document.getElementById('agregarPreview').src       = URL.createObjectURL(this.files[0]);
      document.getElementById('agregarPreviewWrap').style.display = '';
    }
  });

  document.getElementById('agregarIcono').addEventListener('input', function () {
    document.getElementById('agregarIconPreview').className = 'bi bi-' + this.value.trim();
  });

  // ---- MODAL ELIMINAR ----
  const modalEliminar = document.getElementById('modalEliminar');
  modalEliminar.addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('formEliminar').action  = `/admin/imagenes-inicio/${btn.dataset.id}`;
    document.getElementById('eliminarNombre').textContent = btn.dataset.nombre;
  });

})();
</script>
@endpush
