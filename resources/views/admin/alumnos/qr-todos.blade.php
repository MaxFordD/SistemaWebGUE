@extends('layouts.app')

@section('title', 'Carnets QR' . ($seccion ? ' - ' . $seccion->grado . ' ' . $seccion->seccion : ''))

@push('styles')
<style>
.qr-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1.5rem;
}
.qr-tarjeta {
    text-align: center;
    padding: 1rem 0.5rem;
    break-inside: avoid;
}
.qr-tarjeta .qr-wrap svg { width: 100%; height: auto; max-width: 150px; }
.qr-tarjeta .qr-nombre { font-weight: 700; font-size: 0.85rem; margin-top: 0.5rem; line-height: 1.25; }
.qr-tarjeta .qr-dni { color: #6c757d; font-size: 0.75rem; }

@media print {
    .no-print { display: none !important; }
    body * { visibility: hidden; }
    .qr-print-area, .qr-print-area * { visibility: visible; }
    .qr-print-area { position: absolute; top: 0; left: 0; width: 100%; }
    .qr-tarjeta { page-break-inside: avoid; }
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="no-print mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <a href="{{ route('admin.alumnos.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Imprimir todos
        </button>
    </div>

    <div class="qr-print-area">
        <div class="no-print mb-4">
            <h1 class="h4 fw-bold mb-1">Carnets QR</h1>
            @if($seccion)
                <p class="text-muted mb-0">{{ $seccion->grado }} — Sección {{ $seccion->seccion }} ({{ $seccion->turno }})</p>
            @endif
        </div>

        @if($tarjetas->isEmpty())
            <div class="alert alert-info">No hay alumnos activos en esta sección.</div>
        @else
            <div class="qr-grid">
                @foreach($tarjetas as $t)
                    <div class="qr-tarjeta">
                        <div class="qr-wrap">{!! $t['qrSvg'] !!}</div>
                        <div class="qr-nombre">{{ $t['alumno']->apellidos }}, {{ $t['alumno']->nombres }}</div>
                        <div class="qr-dni">DNI: {{ $t['alumno']->dni }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
