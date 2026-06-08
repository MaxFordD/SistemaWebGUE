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
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalImportar">
                <i class="bi bi-file-earmark-arrow-up me-2"></i>Importar CSV
            </button>
            @if($seccion)
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAlumno" onclick="modoCrear()">
                <i class="bi bi-person-plus me-2"></i>Nuevo Alumno
            </button>
            @endif
        </div>
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
        {{-- Formulario borrado masivo --}}
        <form id="formBorrarMasivo" action="{{ route('admin.alumnos.borrarMasivo') }}" method="POST" class="d-none">
            @csrf
            <input type="hidden" name="seccion_id" value="{{ $seccionId }}">
            <input type="hidden" name="año" value="{{ $año }}">
            <div id="inputsMasivo"></div>
        </form>

        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 flex-wrap gap-2">
                <span class="fw-semibold">
                    <i class="bi bi-people me-2 text-primary"></i>
                    {{ $alumnos->count() }} alumno(s) registrado(s)
                </span>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-success">{{ $alumnos->where('estado', 1)->count() }} activos</span>
                    <span class="badge bg-secondary">{{ $alumnos->where('estado', 0)->count() }} inactivos</span>
                    <button type="button" id="btnBorrarSeleccionados" class="btn btn-sm btn-danger d-none"
                            onclick="confirmarBorrarMasivo()">
                        <i class="bi bi-trash me-1"></i>Eliminar seleccionados (<span id="cntSeleccionados">0</span>)
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="44" class="text-center">
                                    <input type="checkbox" id="chkTodos" class="form-check-input"
                                           title="Seleccionar todos" onchange="toggleTodos(this)">
                                </th>
                                <th width="44" class="text-center text-muted small">#</th>
                                <th>Apellidos y Nombres</th>
                                <th width="110">DNI</th>
                                <th width="90" class="text-center">Sexo</th>
                                <th width="130">F. Nacimiento</th>
                                <th width="100" class="text-center">Estado</th>
                                <th width="140" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alumnos as $i => $a)
                            <tr class="{{ $a->estado ? '' : 'table-secondary text-muted' }}" id="fila-{{ $a->alumno_id }}">
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input chk-alumno"
                                           value="{{ $a->alumno_id }}" onchange="actualizarConteo()">
                                </td>
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
                                        <button type="button" class="btn btn-outline-warning" title="Desactivar"
                                            onclick="confirmarDesactivar({{ $a->alumno_id }}, '{{ addslashes($a->apellidos) }}, {{ addslashes($a->nombres) }}')">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" title="Eliminar definitivamente"
                                            onclick="confirmarBorrar({{ $a->alumno_id }}, '{{ addslashes($a->apellidos) }}, {{ addslashes($a->nombres) }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <form id="del-{{ $a->alumno_id }}"
                                          action="{{ route('admin.alumnos.destroy', $a->alumno_id) }}"
                                          method="POST" class="d-none">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="seccion_id" value="{{ $seccionId }}">
                                        <input type="hidden" name="año" value="{{ $año }}">
                                    </form>
                                    <form id="borrar-{{ $a->alumno_id }}"
                                          action="{{ route('admin.alumnos.borrar', $a->alumno_id) }}"
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
{{-- Modal Importar CSV --}}
<div class="modal fade" id="modalImportar" tabindex="-1" aria-labelledby="modalImportarLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalImportarLabel">
                    <i class="bi bi-file-earmark-arrow-up me-2"></i>Importar Alumnos desde CSV
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                {{-- Paso 1: Subir archivo --}}
                <div id="importStep1">
                    <div class="alert alert-info small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        El archivo debe estar separado por <strong>punto y coma (;)</strong>.
                        Columnas esperadas: <code>grado, seccion, apellido, nombre, dni, fecha_nacimiento</code>.
                        Si falta alguna columna requerida, el sistema te pedirá completarla.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Año lectivo</label>
                            <input type="number" id="importAño" class="form-control"
                                   value="{{ $año }}" min="2020" max="2099">
                        </div>
                        <div class="col-md-9">
                            <label class="form-label fw-semibold">Archivo CSV</label>
                            <input type="file" id="importArchivo" accept=".csv,.txt" class="form-control">
                        </div>
                    </div>
                </div>
                {{-- Paso 2: Completar columnas faltantes (generado por JS) --}}
                <div id="importStep2" class="d-none"></div>
                {{-- Paso 3: Resultados (generado por JS) --}}
                <div id="importStep3" class="d-none"></div>
                {{-- Loading --}}
                <div id="importLoading" class="d-none text-center py-5">
                    <div class="spinner-border text-primary mb-3" style="width:2.5rem;height:2.5rem;"></div>
                    <div class="text-muted" id="importLoadingText">Procesando...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" id="btnAnalizar" class="btn btn-primary" onclick="analizarCsv()">
                    <i class="bi bi-search me-1"></i>Analizar archivo
                </button>
                <button type="button" id="btnImportar" class="btn btn-success d-none" onclick="confirmarImportar()">
                    <i class="bi bi-cloud-upload me-1"></i>Importar
                </button>
            </div>
        </div>
    </div>
</div>

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

function confirmarDesactivar(id, nombre) {
    if (confirm(`¿Desactivar al alumno "${nombre}"?\n\nEl alumno quedará inactivo pero sus datos se conservarán.`)) {
        document.getElementById('del-' + id).submit();
    }
}

function confirmarBorrar(id, nombre) {
    if (confirm(`⚠️ ELIMINAR DEFINITIVAMENTE\n\n"${nombre}"\n\nEsto borrará al alumno y TODOS sus registros de asistencia.\n¿Estás seguro? Esta acción no se puede deshacer.`)) {
        document.getElementById('borrar-' + id).submit();
    }
}

function toggleTodos(chk) {
    document.querySelectorAll('.chk-alumno').forEach(c => c.checked = chk.checked);
    actualizarConteo();
}

function actualizarConteo() {
    const seleccionados = document.querySelectorAll('.chk-alumno:checked').length;
    const total = document.querySelectorAll('.chk-alumno').length;
    document.getElementById('cntSeleccionados').textContent = seleccionados;
    document.getElementById('btnBorrarSeleccionados').classList.toggle('d-none', seleccionados === 0);
    document.getElementById('chkTodos').indeterminate = seleccionados > 0 && seleccionados < total;
    document.getElementById('chkTodos').checked = seleccionados === total && total > 0;
}

function confirmarBorrarMasivo() {
    const checks = document.querySelectorAll('.chk-alumno:checked');
    if (!checks.length) return;
    const n = checks.length;
    if (!confirm(`⚠️ ELIMINAR ${n} ALUMNO(S) DEFINITIVAMENTE\n\nSe borrarán los alumnos seleccionados y TODOS sus registros de asistencia.\n¿Estás seguro? Esta acción no se puede deshacer.`)) return;

    const container = document.getElementById('inputsMasivo');
    container.innerHTML = '';
    checks.forEach(c => {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = 'ids[]';
        inp.value = c.value;
        container.appendChild(inp);
    });
    document.getElementById('formBorrarMasivo').submit();
}

// ── Importar CSV ──────────────────────────────────────────────────────────
let importRows   = [];
let importMissing = [];
let importAñoVal = '';

const csrfToken     = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
const previewUrl    = '{{ route("admin.alumnos.importar.preview") }}';
const confirmarUrl  = '{{ route("admin.alumnos.importar.confirmar") }}';

const CAMPO_LABELS = { sexo: 'Sexo', nombres: 'Nombres', apellidos: 'Apellidos', dni: 'DNI', fecha_nacimiento: 'Fecha de nacimiento' };

async function analizarCsv() {
    const file = document.getElementById('importArchivo').files[0];
    importAñoVal = document.getElementById('importAño').value;
    if (!file)          { alert('Selecciona un archivo CSV primero.'); return; }
    if (!importAñoVal)  { alert('Ingresa el año lectivo.'); return; }

    showImportLoading('Analizando archivo...');

    const fd = new FormData();
    fd.append('archivo', file);
    fd.append('año', importAñoVal);
    fd.append('_token', csrfToken);

    try {
        const resp = await fetch(previewUrl, { method: 'POST', body: fd });
        const data = await resp.json();
        hideImportLoading();
        if (!data.ok) { alert('Error: ' + data.error); return; }
        importRows    = data.rows;
        importMissing = data.missing;
        renderImportPaso2(data);
    } catch (e) {
        hideImportLoading();
        alert('Error de conexión con el servidor.');
    }
}

function renderImportPaso2(data) {
    const step2 = document.getElementById('importStep2');

    if (data.missing.length === 0) {
        step2.innerHTML = `
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                Archivo analizado: <strong>${data.total} alumno(s)</strong> listos para importar.
                Sin columnas faltantes.
            </div>`;
        step2.classList.remove('d-none');
        document.getElementById('btnAnalizar').classList.add('d-none');
        document.getElementById('btnImportar').classList.remove('d-none');
        return;
    }

    const faltanTexto = data.missing.map(m => CAMPO_LABELS[m] || m).join(', ');
    let html = `
        <div class="alert alert-warning mb-3">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>${data.total} alumno(s)</strong> encontrados.
            Columna(s) faltante(s): <strong>${faltanTexto}</strong>.
            Completa los datos antes de importar.
        </div>`;

    // Controles rápidos por campo faltante
    if (data.missing.includes('sexo')) {
        html += `
        <div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
            <span class="fw-semibold small text-muted">Aplicar a todos:</span>
            <button type="button" class="btn btn-sm btn-outline-info" onclick="setSexoTodos('M')">
                <i class="bi bi-gender-male me-1"></i>Masculino (M)
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="setSexoTodos('F')"
                    style="border-color:#e91e8c;color:#e91e8c">
                <i class="bi bi-gender-female me-1"></i>Femenino (F)
            </button>
        </div>`;
    }

    // Tabla de alumnos con columnas faltantes editables
    const extraCols = data.missing.map(m => `<th class="text-center" style="min-width:120px">${CAMPO_LABELS[m] || m}</th>`).join('');
    html += `
        <div style="max-height:400px;overflow-y:auto;border:1px solid #dee2e6;border-radius:.375rem;">
            <table class="table table-sm table-bordered align-middle mb-0" id="tablaImport">
                <thead class="table-light" style="position:sticky;top:0;z-index:1;">
                    <tr>
                        <th width="44" class="text-center">#</th>
                        <th>Apellidos y Nombres</th>
                        <th width="100">DNI</th>
                        <th width="110">Grado / Sec.</th>
                        ${extraCols}
                    </tr>
                </thead>
                <tbody>`;

    data.rows.forEach((row, i) => {
        const extraCells = data.missing.map(campo => {
            if (campo === 'sexo') {
                return `<td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <input type="radio" class="btn-check" name="import_sexo_${i}" id="impM_${i}" value="M" autocomplete="off">
                        <label class="btn btn-outline-info py-0 px-2" for="impM_${i}">M</label>
                        <input type="radio" class="btn-check" name="import_sexo_${i}" id="impF_${i}" value="F" autocomplete="off">
                        <label class="btn btn-outline-danger py-0 px-2" for="impF_${i}"
                               style="--bs-btn-hover-border-color:#e91e8c;--bs-btn-active-border-color:#e91e8c">F</label>
                    </div>
                </td>`;
            }
            return `<td><input type="text" class="form-control form-control-sm" name="import_${campo}_${i}" value="${row[campo] ?? ''}"></td>`;
        }).join('');

        html += `<tr>
            <td class="text-center text-muted small">${i + 1}</td>
            <td>${row.apellidos ?? ''}, ${row.nombres ?? ''}</td>
            <td class="font-monospace">${row.dni ?? ''}</td>
            <td class="small">${row.grado ?? ''} ${row.seccion ?? ''}</td>
            ${extraCells}
        </tr>`;
    });

    html += '</tbody></table></div>';
    step2.innerHTML = html;
    step2.classList.remove('d-none');
    document.getElementById('btnAnalizar').classList.add('d-none');
    document.getElementById('btnImportar').classList.remove('d-none');
}

function setSexoTodos(sexo) {
    document.querySelectorAll(`input.btn-check[value="${sexo}"]`).forEach(r => r.checked = true);
}

async function confirmarImportar() {
    // Recoger valores de columnas faltantes
    for (let i = 0; i < importRows.length; i++) {
        for (const campo of importMissing) {
            if (campo === 'sexo') {
                const sel = document.querySelector(`input[name="import_sexo_${i}"]:checked`);
                if (!sel) {
                    alert(`Falta el sexo del alumno #${i + 1}: ${importRows[i].apellidos ?? ''}, ${importRows[i].nombres ?? ''}`);
                    document.getElementById(`impM_${i}`)?.closest('tr')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
                importRows[i].sexo = sel.value;
            } else {
                const el = document.querySelector(`input[name="import_${campo}_${i}"]`);
                if (el) importRows[i][campo] = el.value;
            }
        }
    }

    showImportLoading(`Importando ${importRows.length} alumnos...`);
    document.getElementById('btnImportar').disabled = true;

    const fd = new FormData();
    fd.append('año', importAñoVal);
    fd.append('rows', JSON.stringify(importRows));
    fd.append('_token', csrfToken);

    try {
        const resp = await fetch(confirmarUrl, { method: 'POST', body: fd });
        const data = await resp.json();
        hideImportLoading();
        document.getElementById('btnImportar').disabled = false;
        if (!data.ok) { alert('Error: ' + data.error); return; }
        renderImportResultados(data);
    } catch (e) {
        hideImportLoading();
        document.getElementById('btnImportar').disabled = false;
        alert('Error de conexión con el servidor.');
    }
}

function renderImportResultados(data) {
    let html = `
        <div class="row g-3 mb-3">
            <div class="col-4">
                <div class="card border-success text-center h-100">
                    <div class="card-body py-3">
                        <div class="display-6 fw-bold text-success">${data.inserted}</div>
                        <div class="small text-muted">Importados</div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-warning text-center h-100">
                    <div class="card-body py-3">
                        <div class="display-6 fw-bold text-warning">${data.skipped}</div>
                        <div class="small text-muted">Ya existían (DNI duplicado)</div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-danger text-center h-100">
                    <div class="card-body py-3">
                        <div class="display-6 fw-bold text-danger">${data.errors.length}</div>
                        <div class="small text-muted">Errores</div>
                    </div>
                </div>
            </div>
        </div>`;

    if (data.errors.length > 0) {
        html += `<div class="alert alert-danger" style="max-height:200px;overflow-y:auto;">
            <strong><i class="bi bi-exclamation-triangle me-1"></i>Errores:</strong>
            <ul class="mb-0 mt-2 small">${data.errors.map(e => `<li>${e}</li>`).join('')}</ul>
        </div>`;
    }

    if (data.inserted > 0) {
        html += `
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>
            <strong>${data.inserted}</strong> alumno(s) importados correctamente.
        </div>
        <button class="btn btn-primary w-100" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise me-2"></i>Recargar para ver los cambios
        </button>`;
    }

    document.getElementById('importStep2').classList.add('d-none');
    document.getElementById('importStep3').innerHTML = html;
    document.getElementById('importStep3').classList.remove('d-none');
    document.getElementById('btnImportar').classList.add('d-none');
}

function showImportLoading(text) {
    ['importStep1','importStep2','importStep3'].forEach(id => document.getElementById(id).classList.add('d-none'));
    document.getElementById('importLoadingText').textContent = text;
    document.getElementById('importLoading').classList.remove('d-none');
    document.getElementById('btnAnalizar').disabled = true;
    document.getElementById('btnImportar').disabled = true;
}

function hideImportLoading() {
    document.getElementById('importLoading').classList.add('d-none');
    document.getElementById('importStep1').classList.remove('d-none');
    document.getElementById('btnAnalizar').disabled = false;
}

document.getElementById('modalImportar').addEventListener('hidden.bs.modal', function () {
    ['importStep2','importStep3','importLoading'].forEach(id => document.getElementById(id).classList.add('d-none'));
    document.getElementById('importStep1').classList.remove('d-none');
    document.getElementById('btnAnalizar').classList.remove('d-none');
    document.getElementById('btnAnalizar').disabled = false;
    document.getElementById('btnImportar').classList.add('d-none');
    document.getElementById('btnImportar').disabled = false;
    document.getElementById('importArchivo').value = '';
    importRows = []; importMissing = [];
});
// ── Fin Importar CSV ───────────────────────────────────────────────────────

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
