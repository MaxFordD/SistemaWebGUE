@extends('layouts.app')

@section('title', 'Historial de Asistencia por Sección')

@push('styles')
<style>
.pct-bar { height: 6px; border-radius: 3px; background:#e9ecef; }
.pct-fill { height: 6px; border-radius: 3px; background:#198754; transition: width .4s; }
.pct-fill.warn  { background: #fd7e14; }
.pct-fill.danger { background: #dc3545; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1 fw-bold">
                <i class="bi bi-bar-chart-line me-2 text-primary"></i>Historial por Sección
            </h1>
            <p class="text-muted mb-0">Resumen mensual de asistencia de todos los alumnos</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.asistencia.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-calendar-check me-1"></i>Registro Diario
            </a>
            @if($seccionId)
            <a href="{{ route('admin.asistencia.reporte-pdf', ['seccion_id'=>$seccionId,'mes'=>$mes,'año'=>$año]) }}"
               class="btn btn-danger btn-sm" target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
            </a>
            <a href="{{ route('admin.asistencia.reporte-excel', ['seccion_id'=>$seccionId,'mes'=>$mes,'año'=>$año]) }}"
               class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
            @endif
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.asistencia.historial-seccion') }}" class="row g-3 align-items-end" id="formFiltro">
                <div class="col-md-2 col-6">
                    <label class="form-label fw-semibold small">Año</label>
                    <input type="number" class="form-control" name="año" value="{{ $año }}" min="2020" max="2099">
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label fw-semibold small">Mes</label>
                    <select class="form-select" name="mes" id="selectMes">
                        @foreach($meses as $num => $nombre)
                            <option value="{{ $num }}" {{ $mes == $num ? 'selected' : '' }}>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
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
                        <i class="bi bi-search me-1"></i>Ver resumen
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(!$seccionId)
        <div class="alert alert-info">
            <i class="bi bi-arrow-up-circle me-2"></i>
            Selecciona el año, mes y sección para ver el resumen de asistencia.
        </div>
    @elseif($resumen->isEmpty())
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-circle me-2"></i>
            No hay registros de asistencia para
            <strong>{{ $meses[(int)$mes] }} {{ $año }}</strong> en esta sección.
        </div>
    @else
        {{-- Encabezado de sección --}}
        <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
            <span class="fw-bold fs-5">{{ $seccion->grado }} — Sección {{ $seccion->seccion }}</span>
            <span class="badge bg-{{ $seccion->nivel === 'Primaria' ? 'info' : 'warning' }}">{{ $seccion->nivel }}</span>
            <span class="text-muted small">{{ $meses[(int)$mes] }} {{ $año }}</span>
        </div>

        {{-- Tarjetas resumen global --}}
        @php
            $totAsistio  = $resumen->sum('total_asistio');
            $totFaltas   = $resumen->sum('total_faltas');
            $totTardanza = $resumen->sum('total_tardanzas');
            $totGlobal   = $totAsistio + $totFaltas + $totTardanza;
            $pctAsistencia = $totGlobal > 0 ? round($totAsistio / $totGlobal * 100, 1) : 0;
        @endphp
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="display-6 fw-bold text-success">{{ $totAsistio }}</div>
                    <div class="small text-muted">Total Asistencias</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="display-6 fw-bold text-danger">{{ $totFaltas }}</div>
                    <div class="small text-muted">Total Faltas</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="display-6 fw-bold text-warning">{{ $totTardanza }}</div>
                    <div class="small text-muted">Total Tardanzas</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="display-6 fw-bold {{ $pctAsistencia >= 85 ? 'text-success' : ($pctAsistencia >= 70 ? 'text-warning' : 'text-danger') }}">
                        {{ $pctAsistencia }}%
                    </div>
                    <div class="small text-muted">% Asistencia global</div>
                </div>
            </div>
        </div>

        {{-- Tabla detalle por alumno --}}
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="40" class="text-center">#</th>
                                <th>Apellidos y Nombres</th>
                                <th width="90" class="text-center text-success">Asistió</th>
                                <th width="90" class="text-center text-danger">Faltas</th>
                                <th width="90" class="text-center text-warning">Tardanzas</th>
                                <th width="160">% Asistencia</th>
                                <th width="110" class="text-center">Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resumen as $i => $r)
                            @php
                                $total   = $r->total_asistio + $r->total_faltas + $r->total_tardanzas;
                                $pct     = $total > 0 ? round($r->total_asistio / $total * 100) : 0;
                                $color   = $pct >= 85 ? '' : ($pct >= 70 ? 'warn' : 'danger');
                            @endphp
                            <tr>
                                <td class="text-center text-muted small">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $r->apellidos }}, {{ $r->nombres }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $r->total_asistio }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger">{{ $r->total_faltas }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">{{ $r->total_tardanzas }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="flex-grow-1 pct-bar">
                                            <div class="pct-fill {{ $color }}" style="width:{{ $pct }}%"></div>
                                        </div>
                                        <span class="small fw-semibold {{ $pct >= 85 ? 'text-success' : ($pct >= 70 ? 'text-warning' : 'text-danger') }}"
                                              style="min-width:38px">{{ $pct }}%</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.asistencia.historial-alumno', $r->alumno_id) }}?mes={{ $mes }}&año={{ $año }}"
                                       class="btn btn-sm btn-outline-primary" title="Ver detalle del alumno">
                                        <i class="bi bi-person-lines-fill"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-muted small">
                <i class="bi bi-info-circle me-1"></i>
                {{ $resumen->count() }} alumno(s) | {{ $meses[(int)$mes] }} {{ $año }}
                — Haz clic en <i class="bi bi-person-lines-fill"></i> para ver el historial individual de cada alumno.
            </div>
        </div>
    @endif
</div>
@endsection
