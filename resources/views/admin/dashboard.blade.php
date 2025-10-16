@extends('layouts.app') @section('title', 'Panel de Administración') @section('content') <div class="container-fluid py-4"> {{-- === HEADER === --}}
  <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-2">
    <h1 class="h4 fw-bold mb-0 text-primary"> <i class="bi bi-speedometer2 me-2"></i> Panel de Administración </h1>
    <div class="btn-group"> <a href="{{ route('noticias.create') }}" class="btn btn-primary btn-sm"> <i class="bi bi-plus-circle me-1"></i> Nueva Noticia </a> <a href="{{ route('noticias.index') }}" class="btn btn-outline-secondary btn-sm"> <i class="bi bi-card-list me-1"></i> Ver Noticias </a> </div>
  </div> {{-- === ALERTAS === --}} @php $pendientes = (int)($stats->documentos_pendientes ?? 0); $noLeidos = (int)($stats->mensajes_no_leidos ?? 0); @endphp @if($pendientes > 0) <div class="alert alert-warning d-flex align-items-center fade show shadow-sm"> <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
    <div> <strong>Atención:</strong> {{ $pendientes }} documento(s) están pendientes de revisión. </div>
  </div> @endif @if($noLeidos > 0) <div class="alert alert-info d-flex align-items-center fade show shadow-sm"> <i class="bi bi-envelope-fill me-2 fs-5"></i>
    <div>Tienes <strong>{{ $noLeidos }}</strong> mensaje(s) sin leer.</div>
  </div> @endif {{-- === KPIs === --}} @php $kpis = [ ['label' => 'Usuarios activos', 'value' => $stats->usuarios_activos ?? 0, 'icon' => 'bi-people-fill', 'color' => 'primary'], ['label' => 'Personas activas', 'value' => $stats->personas_activas ?? 0, 'icon' => 'bi-person-badge-fill', 'color' => 'success'], ['label' => 'Roles activos', 'value' => $stats->roles_activos ?? 0, 'icon' => 'bi-shield-lock-fill', 'color' => 'warning'], ['label' => 'Noticias activas', 'value' => $stats->noticias_activas ?? 0, 'icon' => 'bi-newspaper', 'color' => 'info'], ['label' => 'Publicaciones', 'value' => $stats->publicaciones_activas ?? 0, 'icon' => 'bi-megaphone-fill', 'color' => 'danger'], ]; @endphp <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3 mb-4 justify-content-center"> @foreach($kpis as $k) <div class="col">
      <div class="card kpi-card h-100 text-center p-3">
        <div class="d-flex flex-column align-items-center gap-2">
          <div class="kpi-icon bg-{{ $k['color'] }} bg-opacity-10"> <i class="bi {{ $k['icon'] }} text-{{ $k['color'] }} fs-3"></i> </div>
          <div class="small text-muted">{{ $k['label'] }}</div>
          <div class="fs-4 fw-bold text-dark">{{ (int)$k['value'] }}</div>
        </div>
      </div>
    </div> @endforeach </div> {{-- === ATAJOS RÁPIDOS === --}}
  <div class="card shadow-sm mb-4">
    <div class="card-body d-flex flex-wrap gap-2"> <a href="{{ route('noticias.create') }}" class="btn btn-primary btn-sm"> <i class="bi bi-plus-circle"></i> Crear Noticia </a> <a href="{{ route('noticias.index') }}" class="btn btn-outline-secondary btn-sm"> <i class="bi bi-pencil-square"></i> Gestionar Noticias </a> </div>
  </div> {{-- === SECCIONES PRINCIPALES === --}}
  <div class="row g-4"> {{-- Últimas noticias --}}
    <div class="col-12 col-lg-6">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white fw-bold"> <i class="bi bi-newspaper me-1"></i> Últimas Noticias </div>
        <div class="card-body"> @forelse($ultimasNoticias as $n) @php try { $f = is_string($n->fecha_publicacion) ? \Carbon\Carbon::parse($n->fecha_publicacion) : $n->fecha_publicacion; } catch (\Throwable $e) { $f = null; } @endphp <div class="mb-3 pb-3 border-bottom">
            <div class="d-flex justify-content-between"> <a href="{{ route('noticias.show', $n->noticia_id) }}" class="fw-semibold text-decoration-none text-dark"> {{ $n->titulo }} </a> <small class="text-muted"> {{ $f ? $f->setTimezone('America/Lima')->format('d/m/Y H:i') : Str::substr((string)$n->fecha_publicacion,0,16) }} </small> </div>
            <div class="small text-muted"> {{ $n->autor ?? $n->nombre_usuario ?? 'Autor' }} </div>
          </div> @empty <div class="text-muted">Sin registros.</div> @endforelse </div>
        <div class="card-footer bg-white"> <a href="{{ route('noticias.index') }}" class="btn btn-sm btn-outline-secondary"> Ver todas </a> </div>
      </div>
    </div> {{-- === MESA DE PARTES === --}}
    <div class="card shadow-sm mt-4">
      <div class="card-header bg-white fw-bold"> <i class="bi bi-inbox me-1"></i> Mesa de Partes — Pendientes </div>
      <div class="card-body p-0"> @if($mpPendientes->isEmpty()) <div class="p-3 text-muted">Sin documentos pendientes.</div> @else <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Fecha</th>
                <th>Remitente</th>
                <th>Asunto</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody> @foreach($mpPendientes as $d) @php try { $f = is_string($d->fecha_envio) ? \Carbon\Carbon::parse($d->fecha_envio) : $d->fecha_envio; } catch (\Throwable $e) { $f = null; } @endphp <tr>
                <td>{{ $f ? $f->setTimezone('America/Lima')->format('d/m/Y H:i') : Str::substr((string)$d->fecha_envio,0,16) }}</td>
                <td>{{ $d->remitente }}</td>
                <td>{{ $d->asunto }}</td>
                <td><span class="badge bg-warning text-dark">{{ $d->estado }}</span></td>
              </tr> @endforeach </tbody>
          </table>
        </div> @endif </div>
    </div>
  </div> @endsection