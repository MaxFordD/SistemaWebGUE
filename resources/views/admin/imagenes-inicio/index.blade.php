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
.img-card .badge-seccion { font-size: .7rem; }
#previewImg { max-height: 200px; object-fit: contain; }
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

  {{-- Carrusel --}}
  <h5 class="fw-semibold mb-3">
    <i class="bi bi-images me-2 text-primary"></i>Carrusel Principal ({{ $carousel->count() }} diapositivas)
  </h5>
  <div class="row g-3 mb-5">
    @foreach($carousel as $img)
    <div class="col-6 col-md-4 col-lg-3">
      <div class="img-card shadow-sm">
        <img src="{{ asset($img->ruta) }}" alt="{{ $img->alt }}" class="img-thumb"
             onerror="this.src='https://placehold.co/400x200?text=Sin+imagen'">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-1">
            <span class="badge bg-primary badge-seccion">Diapositiva {{ $img->orden }}</span>
          </div>
          <p class="small text-muted mb-2 text-truncate" title="{{ $img->alt }}">{{ $img->alt }}</p>
          <button class="btn btn-warning btn-sm w-100"
                  data-bs-toggle="modal" data-bs-target="#modalEditar"
                  data-id="{{ $img->id }}"
                  data-seccion="{{ $img->seccion }}"
                  data-alt="{{ $img->alt }}"
                  data-titulo=""
                  data-descripcion=""
                  data-icono=""
                  data-src="{{ asset($img->ruta) }}">
            <i class="bi bi-pencil me-1"></i>Cambiar imagen
          </button>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  {{-- Talleres --}}
  <h5 class="fw-semibold mb-3">
    <i class="bi bi-grid me-2 text-success"></i>Tarjetas de Talleres ({{ $talleres->count() }} talleres)
  </h5>
  <div class="row g-3">
    @foreach($talleres as $img)
    <div class="col-6 col-md-4 col-lg-3">
      <div class="img-card shadow-sm">
        <img src="{{ asset($img->ruta) }}" alt="{{ $img->alt }}" class="img-thumb"
             onerror="this.src='https://placehold.co/400x200?text=Sin+imagen'">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-1">
            <span class="badge bg-success badge-seccion">{{ $img->titulo }}</span>
          </div>
          <p class="small text-muted mb-2 text-truncate" title="{{ $img->descripcion }}">{{ $img->descripcion }}</p>
          <button class="btn btn-warning btn-sm w-100"
                  data-bs-toggle="modal" data-bs-target="#modalEditar"
                  data-id="{{ $img->id }}"
                  data-seccion="{{ $img->seccion }}"
                  data-alt="{{ $img->alt }}"
                  data-titulo="{{ $img->titulo }}"
                  data-descripcion="{{ $img->descripcion }}"
                  data-icono="{{ $img->icono }}"
                  data-src="{{ asset($img->ruta) }}">
            <i class="bi bi-pencil me-1"></i>Editar
          </button>
        </div>
      </div>
    </div>
    @endforeach
  </div>

</div>
@endsection

@push('modals')
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

          {{-- Preview actual --}}
          <div class="text-center mb-3">
            <img id="previewImg" src="" alt="Vista previa" class="img-fluid rounded border">
          </div>

          {{-- Nueva foto --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">Nueva imagen <span class="text-muted fw-normal">(JPG, PNG, WEBP · máx. 2 MB)</span></label>
            <input type="file" name="foto" id="inputFoto" class="form-control" accept="image/jpeg,image/png,image/webp">
            <small class="text-muted">Dejar vacío para conservar la imagen actual.</small>
          </div>

          {{-- Alt --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">Texto alternativo <span class="text-danger">*</span></label>
            <input type="text" name="alt" id="inputAlt" class="form-control" maxlength="255" required>
          </div>

          {{-- Campos solo talleres --}}
          <div id="camposTaller">
            <div class="mb-3">
              <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
              <input type="text" name="titulo" id="inputTitulo" class="form-control" maxlength="100">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Descripción <span class="text-danger">*</span></label>
              <input type="text" name="descripcion" id="inputDescripcion" class="form-control" maxlength="255">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Ícono Bootstrap Icons <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i id="iconPreview" class="bi"></i></span>
                <input type="text" name="icono" id="inputIcono" class="form-control" maxlength="50"
                       placeholder="ej: music-note-beamed">
              </div>
              <small class="text-muted">Nombre del ícono sin "bi-". <a href="https://icons.getbootstrap.com/" target="_blank">Ver íconos</a></small>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Guardar cambios
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
  const modal   = document.getElementById('modalEditar');
  const form    = document.getElementById('formEditar');
  const preview = document.getElementById('previewImg');
  const inputFoto = document.getElementById('inputFoto');

  modal.addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const id       = btn.dataset.id;
    const seccion  = btn.dataset.seccion;
    const alt      = btn.dataset.alt;
    const titulo   = btn.dataset.titulo;
    const desc     = btn.dataset.descripcion;
    const icono    = btn.dataset.icono;
    const src      = btn.dataset.src;

    // URL del form
    form.action = `/admin/imagenes-inicio/${id}`;

    // Preview
    preview.src = src;

    // Campos comunes
    document.getElementById('inputAlt').value = alt;

    // Campos taller
    const camposTaller = document.getElementById('camposTaller');
    if (seccion === 'taller') {
      camposTaller.style.display = '';
      document.getElementById('inputTitulo').value      = titulo;
      document.getElementById('inputDescripcion').value = desc;
      document.getElementById('inputIcono').value       = icono;
      document.getElementById('iconPreview').className  = 'bi bi-' + icono;
      document.getElementById('inputTitulo').required      = true;
      document.getElementById('inputDescripcion').required = true;
      document.getElementById('inputIcono').required       = true;
    } else {
      camposTaller.style.display = 'none';
      document.getElementById('inputTitulo').required      = false;
      document.getElementById('inputDescripcion').required = false;
      document.getElementById('inputIcono').required       = false;
    }

    // Limpiar input archivo al abrir
    inputFoto.value = '';
  });

  // Preview local al seleccionar archivo
  inputFoto.addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
      preview.src = URL.createObjectURL(file);
    }
  });

  // Actualizar ícono preview en tiempo real
  document.getElementById('inputIcono').addEventListener('input', function () {
    document.getElementById('iconPreview').className = 'bi bi-' + this.value.trim();
  });
})();
</script>
@endpush
