<style>
.qr-carnet {
    max-width: 320px;
    margin: 0 auto;
    text-align: center;
}
.qr-carnet .qr-wrap svg { width: 100%; height: auto; max-width: 240px; }
@media print {
    .no-print { display: none !important; }
    body * { visibility: hidden; }
    .qr-carnet, .qr-carnet * { visibility: visible; }
    .qr-carnet { position: fixed; top: 0; left: 0; width: 100%; }
}
</style>

<div class="qr-carnet">
    <h6 class="fw-bold mb-1">I.E. José Faustino Sánchez Carrión</h6>
    <p class="text-muted small mb-3">Carnet de asistencia</p>

    <div class="qr-wrap mb-3">
        {!! $qrSvg !!}
    </div>

    <p class="fw-bold fs-5 mb-0">{{ $alumno->apellidos }}, {{ $alumno->nombres }}</p>
    <p class="text-muted mb-1">DNI: {{ $alumno->dni }}</p>
    <p class="mb-0">{{ $alumno->grado }} — Sección {{ $alumno->seccion }}</p>
</div>
