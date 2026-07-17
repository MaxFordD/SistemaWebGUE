<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; margin: 0; }
  h1 { font-size: 15px; color: #7A1A0C; margin: 0 0 2px; }
  h2 { font-size: 12px; color: #555; margin: 0 0 8px; }
  .header { border-bottom: 2px solid #7A1A0C; padding-bottom: 8px; margin-bottom: 12px; }
  .info-grid { display: table; width: 100%; margin-bottom: 12px; }
  .info-col  { display: table-cell; width: 50%; vertical-align: top; }
  .info-col p { margin: 2px 0; }
  .totales { display: table; width: 100%; margin-bottom: 14px; }
  .tot-cell { display: table-cell; text-align: center; padding: 6px; border-radius: 4px; width: 25%; }
  .tot-asistio  { background: #d1e7dd; color: #0f5132; }
  .tot-falta    { background: #f8d7da; color: #842029; }
  .tot-tardanza { background: #fff3cd; color: #664d03; }
  .tot-pct      { background: #e2e3e5; color: #333; }
  .tot-num { font-size: 20px; font-weight: bold; }
  table { width: 100%; border-collapse: collapse; margin-top: 4px; }
  th { background: #7A1A0C; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; }
  td { padding: 5px 8px; border-bottom: 1px solid #e9ecef; font-size: 10px; }
  tr:nth-child(even) td { background: #f8f9fa; }
  .badge-asistio  { background: #d1e7dd; color: #0f5132; padding: 2px 7px; border-radius: 10px; }
  .badge-falta    { background: #f8d7da; color: #842029; padding: 2px 7px; border-radius: 10px; }
  .badge-tardanza { background: #fff3cd; color: #664d03; padding: 2px 7px; border-radius: 10px; }
  .obs { color: #6c757d; font-style: italic; }
  .footer { margin-top: 16px; font-size: 9px; color: #aaa; text-align: right; }
</style>
</head>
<body>

<div class="header">
  <h1>I.E. José Faustino Sánchez Carrión</h1>
  <h2>Reporte de Asistencia Individual — {{ $meses[(int)$mes] }} {{ $año }}</h2>
</div>

<div class="info-grid">
  <div class="info-col">
    <p><strong>Alumno:</strong> {{ $alumno->apellidos }}, {{ $alumno->nombres }}</p>
    <p><strong>DNI:</strong> {{ $alumno->dni ?? '—' }}</p>
  </div>
  <div class="info-col">
    <p><strong>Grado:</strong> {{ $alumno->grado }} — Sección {{ $alumno->seccion }}</p>
    <p><strong>Nivel:</strong> {{ $alumno->nivel }}</p>
  </div>
</div>

@php
  $total = array_sum($totales);
  $pct   = $total > 0 ? round($totales['asistio'] / $total * 100) : 0;
@endphp

<div class="totales">
  <div class="tot-cell tot-asistio">
    <div class="tot-num">{{ $totales['asistio'] }}</div><div>Asistió</div>
  </div>
  <div class="tot-cell tot-falta">
    <div class="tot-num">{{ $totales['falta'] }}</div><div>Faltas</div>
  </div>
  <div class="tot-cell tot-tardanza">
    <div class="tot-num">{{ $totales['tardanza'] }}</div><div>Tardanzas</div>
  </div>
  <div class="tot-cell tot-pct">
    <div class="tot-num">{{ $pct }}%</div><div>Asistencia</div>
  </div>
</div>

<table>
  <thead>
    <tr>
      <th width="30">#</th>
      <th width="80">Fecha</th>
      <th width="80">Día</th>
      <th width="75">Estado</th>
      <th>Observación</th>
      <th width="100">Registrado por</th>
    </tr>
  </thead>
  <tbody>
    @forelse($historial as $i => $reg)
    @php $dt = \Carbon\Carbon::parse($reg->fecha)->locale('es'); @endphp
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>{{ $dt->format('d/m/Y') }}</td>
      <td>{{ ucfirst($dt->isoFormat('dddd')) }}</td>
      <td>
        @if($reg->estado_asistencia === 'Asistio')
          <span class="badge-asistio">Asistió</span>
        @elseif($reg->estado_asistencia === 'Falta')
          <span class="badge-falta">Falta</span>
        @else
          <span class="badge-tardanza">Tardanza</span>
        @endif
      </td>
      <td class="obs">{{ $reg->observacion ?: '—' }}</td>
      <td>{{ $reg->registrado_por }}</td>
    </tr>
    @empty
    <tr><td colspan="6" style="text-align:center;color:#aaa">Sin registros</td></tr>
    @endforelse
  </tbody>
</table>

<div class="footer">Generado el {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
