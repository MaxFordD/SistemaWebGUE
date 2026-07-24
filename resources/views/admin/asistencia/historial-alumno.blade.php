@extends('layouts.app')

@section('title', 'Historial del Alumno')

@push('styles')
<style>
.estado-pill {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px; font-size: .8rem; font-weight: 600;
}
.pill-asistio     { background:#d1e7dd; color:#0f5132; }
.pill-falta       { background:#f8d7da; color:#842029; }
.pill-tardanza    { background:#fff3cd; color:#664d03; }
.pill-justificada { background:#cff4fc; color:#055160; }
.dia-semana    { font-size:.7rem; color:#6c757d; text-transform: uppercase; letter-spacing:.05em; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- Breadcrumb / volver + descargas --}}
    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
        @if($historial->isNotEmpty())
        <div class="d-flex gap-2">
            <a href="{{ route('admin.asistencia.reporte-alumno-pdf', [$alumno->alumno_id, 'mes' => $mes, 'año' => $año]) }}"
               class="btn btn-sm btn-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
            </a>
            <a href="{{ route('admin.asistencia.reporte-alumno-excel', [$alumno->alumno_id, 'mes' => $mes, 'año' => $año]) }}"
               class="btn btn-sm btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
        </div>
        @endif
    </div>

    {{-- Encabezado alumno --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body d-flex align-items-center gap-4 flex-wrap">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                 style="width:64px;height:64px;font-size:1.5rem;flex-shrink:0">
                {{ strtoupper(substr($alumno->nombres, 0, 1)) }}{{ strtoupper(substr($alumno->apellidos, 0, 1)) }}
            </div>
            <div class="flex-grow-1">
                <h2 class="h4 fw-bold mb-1">{{ $alumno->apellidos }}, {{ $alumno->nombres }}</h2>
                <div class="d-flex gap-3 flex-wrap text-muted small">
                    <span><i class="bi bi-card-text me-1"></i>DNI: {{ $alumno->dni }}</span>
                    <span><i class="bi bi-diagram-3 me-1"></i>{{ $alumno->grado }} — Sección {{ $alumno->seccion }}</span>
                    <span><i class="bi bi-mortarboard me-1"></i>{{ $alumno->nivel }}</span>
                    @if($alumno->sexo === 'M')
                        <span><i class="bi bi-gender-male me-1 text-info"></i>Masculino</span>
                    @else
                        <span><i class="bi bi-gender-female me-1" style="color:#e91e8c"></i>Femenino</span>
                    @endif
                </div>
            </div>

            {{-- Tarjetas de totales --}}
            <div class="d-flex gap-2 flex-wrap">
                <div class="text-center px-3 py-2 rounded" style="background:#d1e7dd">
                    <div class="fw-bold fs-4 text-success">{{ $totales['asistio'] }}</div>
                    <div class="small text-success">Asistió</div>
                </div>
                <div class="text-center px-3 py-2 rounded" style="background:#f8d7da">
                    <div class="fw-bold fs-4 text-danger">{{ $totales['falta'] }}</div>
                    <div class="small text-danger">Faltas</div>
                </div>
                <div class="text-center px-3 py-2 rounded" style="background:#fff3cd">
                    <div class="fw-bold fs-4 text-warning">{{ $totales['tardanza'] }}</div>
                    <div class="small" style="color:#664d03">Tardanzas</div>
                </div>
                <div class="text-center px-3 py-2 rounded" style="background:#cff4fc">
                    <div class="fw-bold fs-4" style="color:#055160">{{ $totales['justificada'] }}</div>
                    <div class="small" style="color:#055160">Justificadas</div>
                </div>
                @php
                    $total = array_sum($totales);
                    $pct   = $total > 0 ? round($totales['asistio'] / $total * 100) : 0;
                @endphp
                <div class="text-center px-3 py-2 rounded" style="background:#e2e3e5">
                    <div class="fw-bold fs-4 {{ $pct >= 85 ? 'text-success' : ($pct >= 70 ? 'text-warning' : 'text-danger') }}">
                        {{ $pct }}%
                    </div>
                    <div class="small text-muted">Asistencia</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros mes / año --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.asistencia.historial-alumno', $alumno->alumno_id) }}"
                  class="row g-3 align-items-end">
                <div class="col-md-2 col-6">
                    <label class="form-label fw-semibold small">Año</label>
                    <input type="number" class="form-control" name="año" value="{{ $año }}" min="2020" max="2099">
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label fw-semibold small">Mes</label>
                    <select class="form-select" name="mes">
                        @foreach($meses as $num => $nombre)
                            <option value="{{ $num }}" {{ $mes == $num ? 'selected' : '' }}>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>Filtrar
                    </button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.asistencia.historial-alumno', $alumno->alumno_id) }}"
                       class="btn btn-outline-secondary">Todo el historial</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de historial --}}
    @if($historial->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-calendar-x me-2"></i>
            No hay registros de asistencia para
            <strong>{{ $meses[(int)$mes] }} {{ $año }}</strong>.
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-calendar3 me-2 text-primary"></i>
                {{ $meses[(int)$mes] }} {{ $año }} —
                <span class="text-muted fw-normal">{{ $historial->count() }} registro(s)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="130">Fecha</th>
                                <th width="110" class="text-center">Estado</th>
                                <th>Observación</th>
                                <th width="140" class="text-muted small">Registrado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($historial as $reg)
                            @php
                                $dt = \Carbon\Carbon::parse($reg->fecha)->locale('es');
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $dt->format('d/m/Y') }}</div>
                                    <div class="dia-semana">{{ $dt->isoFormat('dddd') }}</div>
                                </td>
                                <td class="text-center">
                                    @if($reg->estado_asistencia === 'Asistio')
                                        <span class="estado-pill pill-asistio">
                                            <i class="bi bi-check-circle-fill"></i> Asistió
                                        </span>
                                    @elseif($reg->estado_asistencia === 'Falta')
                                        <span class="estado-pill pill-falta">
                                            <i class="bi bi-x-circle-fill"></i> Falta
                                        </span>
                                    @elseif($reg->estado_asistencia === 'Justificada')
                                        <span class="estado-pill pill-justificada">
                                            <i class="bi bi-file-earmark-check-fill"></i> Justificada
                                        </span>
                                    @else
                                        <span class="estado-pill pill-tardanza">
                                            <i class="bi bi-clock-fill"></i> Tardanza
                                        </span>
                                    @endif
                                    @if($reg->hora_registro)
                                        <div class="small text-muted mt-1">{{ substr($reg->hora_registro, 0, 5) }}</div>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $reg->observacion ?: '—' }}
                                    @if($reg->estado_asistencia === 'Justificada' && $reg->motivo_justificacion)
                                        <div class="fst-italic">Motivo: {{ $reg->motivo_justificacion }}</div>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    <i class="bi bi-person me-1"></i>{{ $reg->registrado_por }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Auditoría de cambios --}}
    @if($auditoria->isNotEmpty())
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-clock-history me-2 text-primary"></i>Historial de correcciones
                <span class="text-muted fw-normal small">— quién editó un registro después de creado</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="120">Fecha registro</th>
                                <th width="200">Cambio de estado</th>
                                <th>Observación</th>
                                <th width="150">Editado por</th>
                                <th width="140">Cuándo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($auditoria as $ed)
                            <tr>
                                <td class="small">{{ \Carbon\Carbon::parse($ed->fecha_asistencia)->format('d/m/Y') }}</td>
                                <td class="small">
                                    <span class="text-muted">{{ $ed->estado_anterior ?: '—' }}</span>
                                    <i class="bi bi-arrow-right mx-1"></i>
                                    <strong>{{ $ed->estado_nuevo }}</strong>
                                </td>
                                <td class="small text-muted">
                                    @if($ed->observacion_anterior !== $ed->observacion_nueva)
                                        {{ $ed->observacion_anterior ?: '—' }} <i class="bi bi-arrow-right mx-1"></i> {{ $ed->observacion_nueva ?: '—' }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="small"><i class="bi bi-person me-1"></i>{{ $ed->editado_por }}</td>
                                <td class="small text-muted">{{ \Carbon\Carbon::parse($ed->created_at)->format('d/m/Y H:i') }}</td>
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
