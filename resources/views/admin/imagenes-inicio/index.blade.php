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

/* ── Icon Picker ── */
.icon-picker-wrap { position: relative; }
.icon-picker-btn {
    cursor: pointer;
    text-align: left;
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: .375rem;
    padding: .375rem .75rem;
    display: flex;
    align-items: center;
    gap: .5rem;
    width: 100%;
    transition: border-color .15s, box-shadow .15s;
}
.icon-picker-btn:hover, .icon-picker-btn:focus { border-color: #86b7fe; box-shadow: 0 0 0 .25rem rgba(13,110,253,.25); outline: none; }
.icon-picker-btn .bi { font-size: 1.2rem; min-width: 1.4rem; }
.icon-picker-panel {
    position: absolute;
    z-index: 1060;
    top: calc(100% + 4px);
    left: 0; right: 0;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: .5rem;
    box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.15);
    padding: .75rem;
    display: none;
}
.icon-picker-panel.open { display: block; }
.icon-search { margin-bottom: .5rem; }
.icon-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(44px, 1fr));
    gap: 4px;
    max-height: 220px;
    overflow-y: auto;
}
.icon-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 6px 2px;
    border-radius: .375rem;
    cursor: pointer;
    border: 1px solid transparent;
    transition: background .1s, border-color .1s;
    font-size: .6rem;
    color: #495057;
    gap: 2px;
}
.icon-item:hover { background: #e9f2ff; border-color: #86b7fe; color: #0d6efd; }
.icon-item.selected { background: #0d6efd; color: #fff; border-color: #0d6efd; }
.icon-item .bi { font-size: 1.25rem; }
.icon-no-results { text-align: center; color: #adb5bd; padding: 1rem; font-size: .875rem; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

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
              <label class="form-label fw-semibold">Ícono <span class="text-danger">*</span></label>
              <div class="icon-picker-wrap">
                <input type="hidden" name="icono" id="editIcono" value="">
                <button type="button" class="icon-picker-btn" data-picker="editIcono">
                  <i id="editIconPreview" class="bi bi-star"></i>
                  <span id="editIconLabel" class="text-muted small">Seleccionar ícono...</span>
                </button>
                <div class="icon-picker-panel" id="editIconPanel">
                  <input type="text" class="icon-search form-control form-control-sm" placeholder="Buscar ícono...">
                  <div class="icon-grid"></div>
                </div>
              </div>
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
            <input type="text" name="alt" id="agregarAlt" value="{{ old('alt') }}" class="form-control" maxlength="255" required>
          </div>
          <div id="agregarCamposTaller">
            <div class="mb-3">
              <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
              <input type="text" name="titulo" id="agregarTitulo" value="{{ old('titulo') }}" class="form-control" maxlength="100">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Descripción <span class="text-danger">*</span></label>
              <input type="text" name="descripcion" id="agregarDescripcion" value="{{ old('descripcion') }}" class="form-control" maxlength="255">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Ícono <span class="text-danger">*</span></label>
              <div class="icon-picker-wrap">
                <input type="hidden" name="icono" id="agregarIcono" value="">
                <button type="button" class="icon-picker-btn" data-picker="agregarIcono">
                  <i id="agregarIconPreview" class="bi bi-star"></i>
                  <span id="agregarIconLabel" class="text-muted small">Seleccionar ícono...</span>
                </button>
                <div class="icon-picker-panel" id="agregarIconPanel">
                  <input type="text" class="icon-search form-control form-control-sm" placeholder="Buscar ícono...">
                  <div class="icon-grid"></div>
                </div>
              </div>
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

  // ════════════════════════════════════════════
  //  ICON PICKER
  // ════════════════════════════════════════════
  const ICONS = [
    // Educación
    'book','book-fill','book-half','journal','journal-text','pencil','pencil-fill','pencil-square',
    'mortarboard','mortarboard-fill','backpack','backpack-fill','rulers','calculator','calculator-fill',
    'clipboard','clipboard-fill','clipboard-check','alphabet','translate',
    // Ciencias
    'flask','flask-fill','eyedropper','thermometer','globe','globe2','globe-americas','map',
    'binoculars','binoculars-fill','bug','bug-fill','tree','tree-fill','flower1','flower2','flower3',
    'geo-alt','geo-alt-fill','compass','sun','moon-stars','cloud','lightning','water',
    // Deportes & actividades
    'trophy','trophy-fill','bicycle','person-walking','person-running','basketball',
    'dribbble','dumbbell','activity','hearts','heart-fill','wind',
    // Artes & música
    'music-note','music-note-beamed','music-note-list','music-player','vinyl','headphones',
    'palette','palette-fill','palette2','brush','brush-fill','easel','easel-fill','easel2','easel3',
    'camera','camera-fill','film','image','image-fill','images','collection',
    // Tecnología
    'laptop','laptop-fill','pc','pc-display','cpu','cpu-fill','code-slash','terminal',
    'wifi','broadcast','broadcast-pin','router','usb','motherboard',
    // Personas & comunidad
    'people','people-fill','person','person-fill','person-circle','person-badge','person-check',
    'person-heart','person-raised-hand','person-standing','hand-thumbs-up','emoji-smile',
    // Comunicación
    'chat','chat-fill','chat-dots','megaphone','megaphone-fill','bell','bell-fill',
    'envelope','envelope-fill','newspaper','newspaper','rss','share',
    // Herramientas & otros
    'star','star-fill','heart','award','award-fill','patch-check','patch-star',
    'shield','shield-fill','shield-check','lock','unlock','key','eye','search',
    'house','house-fill','building','buildings','hospital','bank','signpost',
    'tools','wrench','gear','gear-fill','hammer','scissors','paint-bucket',
    'clock','clock-fill','calendar','calendar-fill','calendar-event','hourglass',
    'check-circle','check-circle-fill','x-circle','info-circle','question-circle',
    'plus-circle','arrow-right-circle','arrow-up-circle','send','send-fill',
  ];

  function buildGrid(panel, query) {
    const grid = panel.querySelector('.icon-grid');
    const filtered = query
      ? ICONS.filter(ic => ic.includes(query.toLowerCase()))
      : ICONS;

    if (!filtered.length) {
      grid.innerHTML = '<div class="icon-no-results">Sin resultados</div>';
      return;
    }
    grid.innerHTML = filtered.map(ic =>
      `<div class="icon-item" data-icon="${ic}" title="${ic}">
        <i class="bi bi-${ic}"></i>
        <span>${ic.length > 10 ? ic.slice(0,9)+'…' : ic}</span>
      </div>`
    ).join('');
  }

  function initPicker(btnEl) {
    const targetId = btnEl.dataset.picker;
    const hiddenInput = document.getElementById(targetId);
    const preview     = document.getElementById(targetId.replace('Icono','') + 'IconPreview') ||
                        btnEl.querySelector('.bi');
    const label       = document.getElementById(targetId.replace('Icono','') + 'IconLabel') ||
                        btnEl.querySelector('span');
    const panel       = btnEl.nextElementSibling; // .icon-picker-panel

    // Construir grid inicial
    buildGrid(panel, '');

    // Marcar el seleccionado actual si ya hay valor
    function marcarSeleccionado(val) {
      panel.querySelectorAll('.icon-item').forEach(el => {
        el.classList.toggle('selected', el.dataset.icon === val);
      });
    }

    // Abrir / cerrar
    btnEl.addEventListener('click', function (e) {
      e.stopPropagation();
      const isOpen = panel.classList.contains('open');
      // cerrar todos los demás
      document.querySelectorAll('.icon-picker-panel.open').forEach(p => p.classList.remove('open'));
      if (!isOpen) {
        panel.classList.add('open');
        panel.querySelector('.icon-search').value = '';
        buildGrid(panel, '');
        marcarSeleccionado(hiddenInput.value);
        panel.querySelector('.icon-search').focus();
      }
    });

    // Buscar
    panel.querySelector('.icon-search').addEventListener('input', function () {
      buildGrid(panel, this.value.trim());
      marcarSeleccionado(hiddenInput.value);
    });
    panel.querySelector('.icon-search').addEventListener('click', e => e.stopPropagation());

    // Seleccionar ícono (delegación)
    panel.addEventListener('click', function (e) {
      const item = e.target.closest('.icon-item');
      if (!item) return;
      const val = item.dataset.icon;
      hiddenInput.value = val;
      if (preview) { preview.className = 'bi bi-' + val; }
      if (label)   { label.textContent = val; label.classList.remove('text-muted'); }
      panel.querySelectorAll('.icon-item').forEach(el => el.classList.toggle('selected', el.dataset.icon === val));
      panel.classList.remove('open');
    });
  }

  // Inicializar todos los pickers de la página
  document.querySelectorAll('.icon-picker-btn').forEach(initPicker);

  // Cerrar picker al click fuera
  document.addEventListener('click', () => {
    document.querySelectorAll('.icon-picker-panel.open').forEach(p => p.classList.remove('open'));
  });

  // Función helper: setear ícono desde JS (usada al abrir modales)
  function setIcon(pickerId, val) {
    const btn     = document.querySelector(`[data-picker="${pickerId}"]`);
    const hidden  = document.getElementById(pickerId);
    const preview = document.getElementById(pickerId.replace('Icono','') + 'IconPreview');
    const label   = document.getElementById(pickerId.replace('Icono','') + 'IconLabel');
    if (hidden)  hidden.value = val || '';
    if (preview) preview.className = 'bi bi-' + (val || 'star');
    if (label)   {
      label.textContent = val || 'Seleccionar ícono...';
      label.classList.toggle('text-muted', !val);
    }
  }

  // ════════════════════════════════════════════
  //  MODAL EDITAR
  // ════════════════════════════════════════════
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
      document.getElementById('editTitulo').value      = btn.dataset.titulo      || '';
      document.getElementById('editDescripcion').value = btn.dataset.descripcion || '';
      setIcon('editIcono', btn.dataset.icono || '');
      ['editTitulo','editDescripcion'].forEach(id => document.getElementById(id).required = true);
    } else {
      campos.style.display = 'none';
      ['editTitulo','editDescripcion'].forEach(id => document.getElementById(id).required = false);
    }
  });

  document.getElementById('editFoto').addEventListener('change', function () {
    if (this.files[0]) document.getElementById('editPreview').src = URL.createObjectURL(this.files[0]);
  });

  // ════════════════════════════════════════════
  //  MODAL AGREGAR
  // ════════════════════════════════════════════
  // Si el envio anterior fallo la validacion, se restauran los valores que
  // el usuario ya habia escrito la primera vez que se reabra el modal.
  let restaurarValoresAgregar = @json($errors->any() && old('seccion') ? true : false);

  const modalAgregar = document.getElementById('modalAgregar');
  modalAgregar.addEventListener('show.bs.modal', function (e) {
    const seccion = restaurarValoresAgregar ? @json(old('seccion')) : e.relatedTarget.dataset.seccion;
    document.getElementById('agregarSeccion').value = seccion;

    const esTaller = seccion === 'taller';
    document.getElementById('tituloModalAgregar').textContent = esTaller ? 'Nuevo taller' : 'Nueva diapositiva';
    document.getElementById('agregarCamposTaller').style.display = esTaller ? '' : 'none';
    ['agregarTitulo','agregarDescripcion'].forEach(id => {
      document.getElementById(id).required = esTaller;
      if (!restaurarValoresAgregar) document.getElementById(id).value = '';
    });

    document.getElementById('agregarFoto').value = '';
    if (!restaurarValoresAgregar) document.getElementById('agregarAlt').value = '';
    document.getElementById('agregarPreviewWrap').style.display = 'none';
    setIcon('agregarIcono', '');
    restaurarValoresAgregar = false;
  });

  @if ($errors->any() && old('seccion'))
    new bootstrap.Modal(modalAgregar).show();
  @endif

  document.getElementById('agregarFoto').addEventListener('change', function () {
    if (this.files[0]) {
      document.getElementById('agregarPreview').src = URL.createObjectURL(this.files[0]);
      document.getElementById('agregarPreviewWrap').style.display = '';
    }
  });

  // ════════════════════════════════════════════
  //  MODAL ELIMINAR
  // ════════════════════════════════════════════
  document.getElementById('modalEliminar').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('formEliminar').action       = `/admin/imagenes-inicio/${btn.dataset.id}`;
    document.getElementById('eliminarNombre').textContent = btn.dataset.nombre;
  });

})();
</script>
@endpush
