<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: Arial, sans-serif; font-size: 10px; color: #222; }

  .encabezado { text-align: center; border-bottom: 2px solid #2E4057; padding-bottom: 8px; margin-bottom: 10px; }
  .encabezado h1 { font-size: 13px; font-weight: bold; color: #2E4057; text-transform: uppercase; }
  .encabezado h2 { font-size: 11px; font-weight: normal; color: #444; margin-top: 2px; }
  .encabezado .subtitulo { font-size: 9px; color: #666; margin-top: 3px; }

  .info-bloque { display: table; width: 100%; margin-bottom: 10px; }
  .info-col { display: table-cell; width: 33%; padding: 4px 6px; background: #f5f5f5; border: 1px solid #ddd; }
  .info-col .label { font-size: 8px; color: #888; text-transform: uppercase; letter-spacing: .05em; }
  .info-col .valor { font-size: 10px; font-weight: bold; color: #2E4057; }

  .resumen-totales { display: table; width: 100%; margin-bottom: 12px; }
  .res-item { display: table-cell; text-align: center; padding: 6px; border: 1px solid #ddd; }
  .res-item .num { font-size: 16px; font-weight: bold; }
  .res-item .etiq { font-size: 8px; color: #666; }
  .num-asistio  { color: #198754; }
  .num-falta    { color: #dc3545; }
  .num-tardanza { color: #fd7e14; }
  .num-pct      { color: #0d6efd; }

  table.datos { width: 100%; border-collapse: collapse; }
  table.datos thead tr { background: #2E4057; color: white; }
  table.datos thead th { padding: 5px 6px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: .04em; }
  table.datos tbody tr:nth-child(even) { background: #f8f9fa; }
  table.datos tbody tr:hover { background: #eaf0fb; }
  table.datos td { padding: 4px 6px; border-bottom: 1px solid #e9ecef; font-size: 9.5px; }
  table.datos .centro { text-align: center; }
  .badge-asistio  { background: #d1e7dd; color: #0f5132; padding: 1px 5px; border-radius: 3px; }
  .badge-falta    { background: #f8d7da; color: #842029; padding: 1px 5px; border-radius: 3px; }
  .badge-tardanza { background: #fff3cd; color: #664d03; padding: 1px 5px; border-radius: 3px; }

  .barra-wrap { background: #e9ecef; border-radius: 3px; height: 6px; width: 80px; display: inline-block; vertical-align: middle; }
  .barra-fill { height: 6px; border-radius: 3px; }

  .pie-pagina { margin-top: 14px; border-top: 1px solid #dee2e6; padding-top: 6px;
                font-size: 8px; color: #888; display: table; width: 100%; }
  .pie-izq { display: table-cell; text-align: left; }
  .pie-der { display: table-cell; text-align: right; }
</style>
</head>
<body>

{{-- Encabezado institucional --}}
<div class="encabezado">
  <h1>I.E. Emblemática José Faustino Sánchez Carrión</h1>
  <h2>Reporte de Asistencia — {{ $meses[(int)$mes] }} {{ $año }}</h2>
  <div class="subtitulo">Av. Moche 990, Trujillo, La Libertad — Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</div>
</div>

{{-- Bloque de información --}}
<div class="info-bloque">
  <div class="info-col">
    <div class="label">Grado / Sección</div>
    <div class="valor">{{ $seccion->grado }} — Sección {{ $seccion->seccion }}</div>
  </div>
  <div class="info-col">
    <div class="label">Nivel / Turno</div>
    <div class="valor">{{ $seccion->nivel }} | {{ $seccion->turno }}</div>
  </div>
  <div class="info-col">
    <div class="label">Total alumnos</div>
    <div class="valor">{{ $resumen->count() }}</div>
  </div>
</div>

{{-- Totales globales --}}
@php
  $totAsistio  = $resumen->sum('total_asistio');
  $totFaltas   = $resumen->sum('total_faltas');
  $totTardanza = $resumen->sum('total_tardanzas');
  $totGlobal   = $totAsistio + $totFaltas + $totTardanza;
  $pctGlobal   = $totGlobal > 0 ? round($totAsistio / $totGlobal * 100, 1) : 0;
@endphp
<div class="resumen-totales">
  <div class="res-item">
    <div class="num num-asistio">{{ $totAsistio }}</div>
    <div class="etiq">Total asistencias</div>
  </div>
  <div class="res-item">
    <div class="num num-falta">{{ $totFaltas }}</div>
    <div class="etiq">Total faltas</div>
  </div>
  <div class="res-item">
    <div class="num num-tardanza">{{ $totTardanza }}</div>
    <div class="etiq">Total tardanzas</div>
  </div>
  <div class="res-item">
    <div class="num num-pct">{{ $pctGlobal }}%</div>
    <div class="etiq">% Asistencia global</div>
  </div>
</div>

{{-- Tabla de alumnos --}}
<table class="datos">
  <thead>
    <tr>
      <th width="25">#</th>
      <th>Apellidos y Nombres</th>
      <th class="centro" width="60">Asistió</th>
      <th class="centro" width="50">Faltas</th>
      <th class="centro" width="65">Tardanzas</th>
      <th class="centro" width="55">Total</th>
      <th class="centro" width="110">% Asistencia</th>
    </tr>
  </thead>
  <tbody>
    @foreach($resumen as $i => $r)
    @php
      $total = $r->total_asistio + $r->total_faltas + $r->total_tardanzas;
      $pct   = $total > 0 ? round($r->total_asistio / $total * 100) : 0;
      $color = $pct >= 85 ? '#198754' : ($pct >= 70 ? '#fd7e14' : '#dc3545');
    @endphp
    <tr>
      <td class="centro">{{ $i + 1 }}</td>
      <td>{{ $r->apellidos }}, {{ $r->nombres }}</td>
      <td class="centro"><span class="badge-asistio">{{ $r->total_asistio }}</span></td>
      <td class="centro"><span class="badge-falta">{{ $r->total_faltas }}</span></td>
      <td class="centro"><span class="badge-tardanza">{{ $r->total_tardanzas }}</span></td>
      <td class="centro">{{ $total }}</td>
      <td class="centro">
        <span class="barra-wrap">
          <span class="barra-fill" style="width:{{ $pct }}%;background:{{ $color }};"></span>
        </span>
        <span style="color:{{ $color }};font-weight:bold;margin-left:4px;">{{ $pct }}%</span>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

{{-- Pie de página --}}
<div class="pie-pagina">
  <div class="pie-izq">I.E. José Faustino Sánchez Carrión — Sistema Web Institucional</div>
  <div class="pie-der">Reporte generado automáticamente</div>
</div>

</body>
</html>
