@extends('layouts.app')
@section('title', 'Editar Nosotros')

@push('styles')
<style>
  /* ── Tabs ──
     La pestaña activa ya se pinta de vino (fondo) por el estilo global
     "clay" del panel admin; aquí solo definimos el color del texto para
     que combine, en vez de dejar el azul de Bootstrap por defecto. */
  .nosotros-tab-btn { border-radius: .375rem .375rem 0 0 !important; font-size: .875rem; }
  .nav-link.nosotros-tab-btn        { color: #495057; }
  .nav-link.nosotros-tab-btn.active { color: #fff; font-weight: 600; }

  /* ── Pilares ── */
  .pilar-card-admin { border-left: 4px solid #dee2e6; transition: border-color .2s; }
  .pilar-card-admin:hover { border-left-color: var(--bs-primary); }

  /* ── Labels ── */
  .form-label.fw-semibold,
  .form-label.fw-bold              { color: #1a202c; }
  .form-label.small.fw-semibold    { color: #374151 !important; font-size: .78rem !important; }

  /* ── Section badge ── */
  .section-badge {
    font-size: .72rem; letter-spacing: .05em;
    padding: .25rem .65rem; border-radius: 2rem; font-weight: 600;
    background: rgba(10,88,202,.1) !important; color: #0a4abf !important;
  }

  /* ── Misc ── */
  .color-dot { width:14px; height:14px; border-radius:50%; display:inline-block; border:1px solid rgba(0,0,0,.15); }
  .norma-row { cursor: default; }
  .img-preview-box { width:100%; aspect-ratio:16/9; object-fit:cover; border-radius:.5rem; border:2px dashed #dee2e6; }

  /* ── Botón de icono ── */
  .icon-pick-btn {
    width: 42px; height: 34px; padding: 0; font-size: 1.25rem;
    border: 1px solid #dee2e6; border-radius: .375rem; background: #f8f9fa;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; transition: background .12s, border-color .12s;
  }
  .icon-pick-btn:hover { background: #e9ecef; border-color: #adb5bd; }

  /* ── Dropdown de iconos ── */
  #iconPickerDropdown {
    display: none; position: fixed; z-index: 1055; width: 314px;
    background: #fff; border: 1px solid #dee2e6;
    border-radius: .5rem; box-shadow: 0 8px 24px rgba(0,0,0,.12);
  }
  #iconGrid {
    display: grid; grid-template-columns: repeat(8,1fr);
    gap: 2px; max-height: 240px; overflow-y: auto;
  }
  .icon-grid-item {
    border: 1px solid transparent; border-radius: .3rem; background: transparent;
    color: #374151; font-size: 1.15rem; padding: .32rem;
    line-height: 1; cursor: pointer; transition: all .1s;
  }
  .icon-grid-item:hover { background: #dbeafe; border-color: #93c5fd; color: #1d4ed8; }

  /* ── Paleta de colores ── */
  .color-palette { display: flex; flex-wrap: wrap; gap: 5px; align-items: center; }
  .color-swatch {
    width: 22px; height: 22px; border-radius: 50%; border: 2px solid transparent;
    padding: 0; cursor: pointer; transition: transform .12s, box-shadow .12s; outline: none;
  }
  .color-swatch:hover  { transform: scale(1.2); }
  .color-swatch.active { border-color: #1a202c; box-shadow: 0 0 0 2px #fff inset; transform: scale(1.1); }

  /* ── Panel de contenido de las pestañas: crema en vez de blanco, más redondeado ── */
  .nosotros-tab-content {
    background: var(--secondary-subtle) !important;
    border-radius: 0 0 1.125rem 1.125rem !important;
  }
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width:1100px;">

  {{-- Header --}}
  <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
    <div>
      <h1 class="h4 fw-bold mb-0 text-primary"><i class="bi bi-pencil-square me-2"></i>Editar página "Nosotros"</h1>
      <small class="text-muted">Los cambios se reflejan inmediatamente en el sitio público</small>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('nosotros') }}" target="_blank" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-eye me-1"></i>Ver página
      </a>
      <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Dashboard
      </a>
    </div>
  </div>

  <form action="{{ route('admin.nosotros.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-0" id="nosotrosTabs">
      <li class="nav-item">
        <button class="nav-link nosotros-tab-btn active" data-bs-toggle="tab" data-bs-target="#tab-historia" type="button">
          <i class="bi bi-clock-history me-1"></i>Historia
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link nosotros-tab-btn" data-bs-toggle="tab" data-bs-target="#tab-mision" type="button">
          <i class="bi bi-bullseye me-1"></i>Misión y Visión
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link nosotros-tab-btn" data-bs-toggle="tab" data-bs-target="#tab-pilares" type="button">
          <i class="bi bi-columns-gap me-1"></i>Pilares
          <span class="badge bg-secondary ms-1">{{ count($pilares) }}</span>
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link nosotros-tab-btn" data-bs-toggle="tab" data-bs-target="#tab-normas" type="button">
          <i class="bi bi-list-ol me-1"></i>Normas
          <span class="badge bg-secondary ms-1">{{ count($normas) }}</span>
        </button>
      </li>
    </ul>

    <div class="tab-content nosotros-tab-content border border-top-0 shadow-sm mb-4">

      {{-- ===== TAB: HISTORIA ===== --}}
      <div class="tab-pane fade show active p-4" id="tab-historia">
        <div class="d-flex align-items-center mb-4">
          <span class="section-badge me-2">
            <i class="bi bi-clock-history me-1"></i>Sección Historia
          </span>
        </div>

        <div class="row g-4">
          <div class="col-lg-7">
            <div class="mb-3">
              <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
              <input type="text" name="historia_titulo"
                     class="form-control @error('historia_titulo') is-invalid @enderror"
                     value="{{ old('historia_titulo', $data['historia_titulo'] ?? 'Nuestra Historia') }}"
                     placeholder="Ej: Nuestra Historia">
              @error('historia_titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Párrafo 1 <span class="text-danger">*</span></label>
              <textarea name="historia_p1" id="historia_p1" rows="4"
                        class="form-control @error('historia_p1') is-invalid @enderror"
                        placeholder="Texto introductorio...">{{ old('historia_p1', $data['historia_p1'] ?? '') }}</textarea>
              @error('historia_p1')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Párrafo 2 <span class="text-danger">*</span></label>
              <textarea name="historia_p2" id="historia_p2" rows="4"
                        class="form-control @error('historia_p2') is-invalid @enderror"
                        placeholder="Texto complementario...">{{ old('historia_p2', $data['historia_p2'] ?? '') }}</textarea>
              @error('historia_p2')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="col-lg-5">
            <label class="form-label fw-semibold">Fotos del carrusel</label>
            <div id="historia-imagenes-grid" class="row g-2 mb-2">
              @foreach($historiaImagenes as $img)
                @php
                  $imgUrl = str_starts_with($img, 'images/') ? asset($img) : asset('storage/' . $img);
                @endphp
                <div class="col-4 historia-imagen-item">
                  <div class="position-relative">
                    <img src="{{ $imgUrl }}" class="img-preview-box w-100" alt="Foto de la historia">
                    <input type="hidden" name="historia_imagenes_actuales[]" value="{{ $img }}">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                            style="padding:.1rem .35rem;" title="Quitar"
                            onclick="this.closest('.historia-imagen-item').remove()">
                      <i class="bi bi-x"></i>
                    </button>
                  </div>
                </div>
              @endforeach
            </div>
            <label class="btn btn-outline-primary btn-sm w-100 mb-1">
              <i class="bi bi-upload me-1"></i>Agregar fotos
              <input type="file" name="historia_imagenes_nuevas[]" accept="image/jpeg,image/png,image/webp"
                     class="d-none" multiple onchange="agregarImagenesHistoria(this)">
            </label>
            <small class="text-muted d-block text-center">JPG, PNG o WEBP · Máx. 4 MB c/u. Se muestran en el orden en que quedan aquí.</small>
          </div>
        </div>

        <div class="border-top pt-3 mt-2 d-flex justify-content-end">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-floppy me-1"></i>Guardar cambios
          </button>
        </div>
      </div>

      {{-- ===== TAB: MISIÓN Y VISIÓN ===== --}}
      <div class="tab-pane fade p-4" id="tab-mision">
        <div class="d-flex align-items-center mb-4">
          <span class="section-badge">
            <i class="bi bi-bullseye me-1"></i>Misión y Visión
          </span>
        </div>

        <div class="row g-4">
          <div class="col-md-6">
            <div class="card h-100 border-0 bg-light">
              <div class="card-body">
                <label class="form-label fw-bold mb-2">
                  <i class="bi bi-bullseye text-primary me-1"></i>Nuestra Misión <span class="text-danger">*</span>
                </label>
                <textarea name="mision" id="mision" rows="7"
                          class="form-control @error('mision') is-invalid @enderror"
                          placeholder="Descripción de la misión institucional...">{{ old('mision', $data['mision'] ?? '') }}</textarea>
                @error('mision')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card h-100 border-0 bg-light">
              <div class="card-body">
                <label class="form-label fw-bold mb-2">
                  <i class="bi bi-eye text-primary me-1"></i>Nuestra Visión <span class="text-danger">*</span>
                </label>
                <textarea name="vision" id="vision" rows="7"
                          class="form-control @error('vision') is-invalid @enderror"
                          placeholder="Descripción de la visión institucional...">{{ old('vision', $data['vision'] ?? '') }}</textarea>
                @error('vision')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
        </div>

        <div class="border-top pt-3 mt-4 d-flex justify-content-end">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-floppy me-1"></i>Guardar cambios
          </button>
        </div>
      </div>

      {{-- ===== TAB: PILARES ===== --}}
      <div class="tab-pane fade p-4" id="tab-pilares">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div>
            <span class="section-badge">
              <i class="bi bi-columns-gap me-1"></i>Pilares Institucionales
            </span>
            <small class="text-muted ms-2">
              Clic en el icono para cambiarlo ·
              <a href="https://icons.getbootstrap.com/" target="_blank" class="ms-1 small">
                <i class="bi bi-box-arrow-up-right"></i> Ver catálogo
              </a>
            </small>
          </div>
          <button type="button" class="btn btn-sm btn-success" onclick="agregarPilar()">
            <i class="bi bi-plus-lg me-1"></i>Agregar pilar
          </button>
        </div>

        @php
          $colorHex   = ['primary'=>'#0d6efd','secondary'=>'#6c757d','success'=>'#198754',
                         'danger'=>'#dc3545','warning'=>'#ffc107','info'=>'#0dcaf0','dark'=>'#212529'];
          $colorLabel = ['primary'=>'Azul','secondary'=>'Gris','success'=>'Verde',
                         'danger'=>'Rojo','warning'=>'Amarillo','info'=>'Celeste','dark'=>'Negro'];
        @endphp

        <div id="pilares-container">
          @forelse($pilares as $i => $pilar)
          <div class="pilar-card-admin card mb-3 border shadow-sm" style="border-left-color:{{ $colorHex[$pilar['color']] ?? '#dee2e6' }} !important;">
            <div class="card-body py-3">
              <div class="row g-3 align-items-start">

                {{-- Icono --}}
                <div class="col-md-2">
                  <label class="form-label small fw-semibold mb-1">Icono</label>
                  <div>
                    <button type="button" class="icon-pick-btn"
                            onclick="openIconPicker(this,'prev-icon-{{ $i }}','icon-val-{{ $i }}')"
                            title="Cambiar icono">
                      <i class="bi bi-{{ $pilar['icon'] }}" id="prev-icon-{{ $i }}"></i>
                    </button>
                    <input type="hidden" name="pilar_icon[]" id="icon-val-{{ $i }}" value="{{ $pilar['icon'] }}">
                  </div>
                </div>

                {{-- Título --}}
                <div class="col-md-3">
                  <label class="form-label small fw-semibold mb-1">Título</label>
                  <input type="text" name="pilar_titulo[]" class="form-control form-control-sm"
                         value="{{ $pilar['titulo'] }}" placeholder="Ej: Valores" required>
                </div>

                {{-- Color --}}
                <div class="col-md-2">
                  <label class="form-label small fw-semibold mb-1">Color</label>
                  <div class="color-palette">
                    @foreach(['primary','secondary','success','danger','warning','info','dark'] as $c)
                    <button type="button"
                            class="color-swatch {{ $pilar['color'] === $c ? 'active' : '' }}"
                            data-color="{{ $c }}"
                            style="background:{{ $colorHex[$c] }}"
                            onclick="selectColor(this, this.closest('.pilar-card-admin'))"
                            title="{{ $colorLabel[$c] }}"></button>
                    @endforeach
                    <input type="hidden" name="pilar_color[]" value="{{ $pilar['color'] }}">
                  </div>
                </div>

                {{-- Descripción con editor --}}
                <div class="col-md-4">
                  <label class="form-label small fw-semibold mb-1">Descripción</label>
                  <textarea name="pilar_desc[]" id="pilar_desc_{{ $i }}" rows="2"
                            class="form-control form-control-sm"
                            placeholder="Breve descripción...">{{ $pilar['desc'] }}</textarea>
                </div>

                {{-- Eliminar --}}
                <div class="col-md-1 d-flex align-items-end" style="padding-top:1.5rem;">
                  <button type="button" class="btn btn-sm btn-outline-danger w-100"
                          onclick="removePilar(this)" title="Eliminar">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>

              </div>
            </div>
          </div>
          @empty
          <div id="pilares-empty" class="text-center text-muted py-5 border rounded bg-light">
            <i class="bi bi-columns-gap fs-1 opacity-25"></i>
            <p class="mt-2">No hay pilares. Haz clic en "Agregar pilar".</p>
          </div>
          @endforelse
        </div>

        <div class="border-top pt-3 mt-2 d-flex justify-content-end">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-floppy me-1"></i>Guardar cambios
          </button>
        </div>
      </div>

      {{-- ===== TAB: NORMAS ===== --}}
      <div class="tab-pane fade p-4" id="tab-normas">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <span class="section-badge">
            <i class="bi bi-list-ol me-1"></i>Normas de Convivencia
          </span>
          <button type="button" class="btn btn-sm btn-success" onclick="agregarNorma()">
            <i class="bi bi-plus-lg me-1"></i>Agregar norma
          </button>
        </div>

        <div id="normas-container" class="col-lg-8 mx-auto">
          @forelse($normas as $i => $norma)
          <div class="norma-row input-group mb-2 shadow-sm">
            <span class="input-group-text fw-bold text-white" style="background:#7B1C3A; min-width:46px;">
              {{ str_pad($i+1, 2, '0', STR_PAD_LEFT) }}
            </span>
            <input type="text" name="normas[]" class="form-control" value="{{ $norma }}"
                   placeholder="Escribe la norma...">
            <button type="button" class="btn btn-outline-danger"
                    onclick="this.closest('.norma-row').remove(); renumerarNormas()" title="Eliminar">
              <i class="bi bi-trash"></i>
            </button>
          </div>
          @empty
          <div id="normas-empty" class="text-center text-muted py-5 border rounded bg-light">
            <i class="bi bi-list-ol fs-1 opacity-25"></i>
            <p class="mt-2">No hay normas. Haz clic en "Agregar norma".</p>
          </div>
          @endforelse
        </div>

        <div class="border-top pt-3 mt-4 col-lg-8 mx-auto d-flex justify-content-end">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-floppy me-1"></i>Guardar cambios
          </button>
        </div>
      </div>

    </div>
  </form>
</div>

{{-- Dropdown selector de iconos --}}
<div id="iconPickerDropdown">
  <div class="px-2 pt-2 pb-1 border-bottom d-flex align-items-center justify-content-between">
    <span class="fw-semibold" style="font-size:.78rem; color:#374151;">
      <i class="bi bi-grid-3x3-gap me-1 text-primary"></i>Seleccionar icono
    </span>
    <button type="button" class="btn-close btn-sm" onclick="closeIconPicker()"></button>
  </div>
  <div class="p-2">
    <input type="text" id="iconSearch" class="form-control form-control-sm mb-1"
           placeholder="Buscar..." oninput="renderIconGrid(this.value)">
    <div id="iconGrid"></div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/tinymce/js/tinymce/tinymce.min.js') }}"></script>
<script>
/* ════════════════════════════════════════
   TINYMCE
════════════════════════════════════════ */
const _TCE_CFG = {
  menubar: false,
  language: 'es',
  plugins: ['link', 'lists', 'wordcount'],
  toolbar: 'bold italic underline | bullist numlist | link | removeformat',
  branding: false,
  promotion: false,
  entity_encoding: 'raw',
  content_style: 'body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;font-size:14px;line-height:1.6;margin:8px;}',
  setup: ed => ed.on('change input', () => ed.save())
};

function initEditor(selector, height) {
  // No reinicializar si ya existe
  const id = selector.replace('#', '');
  if (tinymce.get(id)) return;
  if (!document.querySelector(selector)) return;
  tinymce.init(Object.assign({}, _TCE_CFG, { selector, height: height || 160 }));
}

// Historia (tab activo al cargar)
initEditor('#historia_p1', 160);
initEditor('#historia_p2', 160);

// Tabs ocultos: inicializar al hacer clic
document.querySelectorAll('[data-bs-toggle="tab"]').forEach(btn => {
  btn.addEventListener('shown.bs.tab', function(e) {
    const target = e.target.dataset.bsTarget;

    if (target === '#tab-mision') {
      initEditor('#mision', 200);
      initEditor('#vision', 200);
    }

    if (target === '#tab-pilares') {
      document.querySelectorAll('[id^="pilar_desc_"]').forEach(ta => {
        initEditor('#' + ta.id, 120);
      });
    }
  });
});

// Guardar TinyMCE antes del submit
document.querySelector('form').addEventListener('submit', () => tinymce.triggerSave());

/* ════════════════════════════════════════
   COLORES / CONSTANTES
════════════════════════════════════════ */
const COLORS = ['primary','secondary','success','danger','warning','info','dark'];
const COLOR_HEX = {
  primary:'#0d6efd', secondary:'#6c757d', success:'#198754',
  danger:'#dc3545',  warning:'#ffc107',   info:'#0dcaf0', dark:'#212529'
};
const COLOR_LABEL = {
  primary:'Azul', secondary:'Gris', success:'Verde',
  danger:'Rojo',  warning:'Amarillo', info:'Celeste', dark:'Negro'
};

/* ════════════════════════════════════════
   ICON PICKER
════════════════════════════════════════ */
const ICON_LIST = [
  'star-fill','heart-fill','book-fill','people-fill','shield-fill','trophy-fill',
  'lightbulb-fill','graph-up','check-circle-fill','hand-thumbs-up-fill',
  'mortarboard-fill','pencil-fill','gear-fill','globe','house-fill','calendar-fill',
  'chat-dots-fill','bell-fill','flag-fill','award-fill','building-fill',
  'puzzle-fill','eye-fill','bullseye','compass-fill','person-check-fill',
  'columns-gap','layers-fill','diagram-3-fill','megaphone-fill','person-fill',
  'lightning-fill','palette-fill','bar-chart-fill','pie-chart-fill','map-fill',
  'clock-fill','lock-fill','file-text-fill','folder-fill','cloud-fill',
  'phone-fill','envelope-fill','star','heart','book','people','shield','trophy',
  'lightbulb','check-circle','mortarboard','pencil','gear','box-seam',
  'backpack-fill','journal-text','globe2','signpost-fill','clipboard-check-fill',
  'recycle','tree-fill','camera-fill','mic-fill','music-note-beamed','flower1',
  'leaf','sun-fill','diamond-fill','infinity','person-arms-up','emoji-smile-fill',
  'chat-square-heart-fill','patch-check-fill','bookmark-star-fill','briefcase-fill',
  'tools','wrench-adjustable-fill','rocket-fill','globe-americas','flag-fill'
];

let _iconTarget = null;

function openIconPicker(btn, previewId, inputId) {
  _iconTarget = { previewId, inputId };
  const dp   = document.getElementById('iconPickerDropdown');
  const rect = btn.getBoundingClientRect();
  let left   = rect.left;
  if (left + 314 > window.innerWidth - 8) left = window.innerWidth - 314 - 8;
  dp.style.top  = (rect.bottom + 6) + 'px';
  dp.style.left = left + 'px';
  document.getElementById('iconSearch').value = '';
  renderIconGrid('');
  dp.style.display = 'block';
}

function closeIconPicker() {
  document.getElementById('iconPickerDropdown').style.display = 'none';
}

function renderIconGrid(q) {
  const list = q.trim() ? ICON_LIST.filter(ic => ic.includes(q.toLowerCase().trim())) : ICON_LIST;
  document.getElementById('iconGrid').innerHTML = list.length
    ? list.map(ic => `<button type="button" class="icon-grid-item" onclick="selectIcon('${ic}')" title="${ic}"><i class="bi bi-${ic}"></i></button>`).join('')
    : `<p class="text-muted text-center py-3 mb-0 small">Sin resultados</p>`;
}

function selectIcon(icon) {
  if (_iconTarget) {
    document.getElementById(_iconTarget.inputId).value = icon;
    const el = document.getElementById(_iconTarget.previewId);
    if (el) el.className = 'bi bi-' + icon;
  }
  closeIconPicker();
}

document.addEventListener('click', e => {
  const dp = document.getElementById('iconPickerDropdown');
  if (dp.style.display === 'block' && !dp.contains(e.target) && !e.target.closest('.icon-pick-btn'))
    closeIconPicker();
});

/* ════════════════════════════════════════
   COLOR PALETTE
════════════════════════════════════════ */
function selectColor(swatchBtn, card) {
  const color = swatchBtn.dataset.color;
  swatchBtn.closest('.color-palette').querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
  swatchBtn.classList.add('active');
  const input = swatchBtn.closest('.color-palette').querySelector('input[name="pilar_color[]"]');
  if (input) input.value = color;
  if (card) card.style.borderLeftColor = COLOR_HEX[color] || '#dee2e6';
}

/* ════════════════════════════════════════
   FOTOS DEL CARRUSEL DE HISTORIA
════════════════════════════════════════ */
let historiaNuevosArchivos = [];

function agregarImagenesHistoria(input) {
  const grid = document.getElementById('historia-imagenes-grid');

  Array.from(input.files).forEach(file => {
    historiaNuevosArchivos.push(file);

    const reader = new FileReader();
    reader.onload = e => {
      const col = document.createElement('div');
      col.className = 'col-4 historia-imagen-item';
      col.innerHTML = `
        <div class="position-relative">
          <img src="${e.target.result}" class="img-preview-box w-100" alt="Nueva foto">
          <span class="badge bg-success position-absolute top-0 start-0 m-1">Nueva</span>
          <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                  style="padding:.1rem .35rem;" title="Quitar">
            <i class="bi bi-x"></i>
          </button>
        </div>`;
      col.querySelector('button').addEventListener('click', () => {
        const idx = historiaNuevosArchivos.indexOf(file);
        if (idx > -1) historiaNuevosArchivos.splice(idx, 1);
        col.remove();
        sincronizarInputImagenesHistoria();
      });
      grid.appendChild(col);
    };
    reader.readAsDataURL(file);
  });

  sincronizarInputImagenesHistoria();
  input.value = '';
}

function sincronizarInputImagenesHistoria() {
  const dt = new DataTransfer();
  historiaNuevosArchivos.forEach(f => dt.items.add(f));
  document.querySelector('input[name="historia_imagenes_nuevas[]"]').files = dt.files;
}

/* ════════════════════════════════════════
   PILARES
════════════════════════════════════════ */
function removePilar(btn) {
  const card = btn.closest('.pilar-card-admin');
  const desc = card.querySelector('[id^="pilar_desc_"]');
  if (desc && tinymce.get(desc.id)) tinymce.get(desc.id).remove();
  card.remove();
}

function agregarPilar() {
  document.getElementById('pilares-empty')?.remove();
  const uid = Date.now();
  const swatches = COLORS.map(c =>
    `<button type="button" class="color-swatch${c==='primary'?' active':''}"
             data-color="${c}" style="background:${COLOR_HEX[c]}"
             onclick="selectColor(this,this.closest('.pilar-card-admin'))"
             title="${COLOR_LABEL[c]}"></button>`
  ).join('');

  const html = `
    <div class="pilar-card-admin card mb-3 border shadow-sm" style="border-left:4px solid #0d6efd;">
      <div class="card-body py-3">
        <div class="row g-3 align-items-start">
          <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Icono</label>
            <div>
              <button type="button" class="icon-pick-btn"
                      onclick="openIconPicker(this,'prev-icon-${uid}','icon-val-${uid}')"
                      title="Cambiar icono">
                <i class="bi bi-star-fill" id="prev-icon-${uid}"></i>
              </button>
              <input type="hidden" name="pilar_icon[]" id="icon-val-${uid}" value="star-fill">
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Título</label>
            <input type="text" name="pilar_titulo[]" class="form-control form-control-sm" placeholder="Ej: Valores" required>
          </div>
          <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Color</label>
            <div class="color-palette">
              ${swatches}
              <input type="hidden" name="pilar_color[]" value="primary">
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold mb-1">Descripción</label>
            <textarea name="pilar_desc[]" id="pilar_desc_${uid}" rows="2"
                      class="form-control form-control-sm" placeholder="Breve descripción..."></textarea>
          </div>
          <div class="col-md-1 d-flex align-items-end" style="padding-top:1.5rem;">
            <button type="button" class="btn btn-sm btn-outline-danger w-100"
                    onclick="removePilar(this)" title="Eliminar">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>
    </div>`;

  document.getElementById('pilares-container').insertAdjacentHTML('beforeend', html);

  // Inicializar TinyMCE en la descripción del nuevo pilar
  initEditor('#pilar_desc_' + uid, 120);
}

/* ════════════════════════════════════════
   NORMAS
════════════════════════════════════════ */
function agregarNorma() {
  document.getElementById('normas-empty')?.remove();
  const total = document.querySelectorAll('#normas-container .norma-row').length + 1;
  const num   = String(total).padStart(2, '0');
  const html  = `
    <div class="norma-row input-group mb-2 shadow-sm">
      <span class="input-group-text fw-bold text-white" style="background:#7B1C3A; min-width:46px;">${num}</span>
      <input type="text" name="normas[]" class="form-control" placeholder="Escribe la norma...">
      <button type="button" class="btn btn-outline-danger"
              onclick="this.closest('.norma-row').remove(); renumerarNormas()" title="Eliminar">
        <i class="bi bi-trash"></i>
      </button>
    </div>`;
  document.getElementById('normas-container').insertAdjacentHTML('beforeend', html);
}

function renumerarNormas() {
  document.querySelectorAll('#normas-container .norma-row').forEach((row, i) => {
    const el = row.querySelector('.input-group-text');
    if (el) el.textContent = String(i + 1).padStart(2, '0');
  });
}
</script>
@endpush
