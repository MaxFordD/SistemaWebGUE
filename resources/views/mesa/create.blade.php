@extends('layouts.app')

@section('title', 'Mesa de Partes')
@section('body_class', 'waves-compact')

@section('content')
<div class="container py-4">
    <h1 class="h3 fw-bold mb-3">Mesa de Partes</h1>

    {{-- FUT (Formato Único de Trámite) – enlace a plantilla --}}
    <div class="alert alert-info d-flex align-items-center justify-content-between rounded-4 shadow-sm mb-3">
        <div class="me-3">
            <strong>¿Necesitas el FUT?</strong>
            <span class="d-block small">Descarga el Formato Único de Trámite (FUT) antes de completar tu solicitud.</span>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-primary" href="{{ asset('docs/FUT GUE 2024 - EN BLANCO.pdf') }}" target="_blank" rel="noopener">Ver modelo</a>
            <a class="btn btn-sm btn-primary" href="{{ asset('docs/FUT GUE 2024 - EN BLANCO.pdf') }}" download>Descargar FUT</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('mesa.store') }}" method="post" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="remitente" class="form-label">Remitente</label>
                        <input type="text" id="remitente" name="remitente" class="form-control" required aria-required="true">
                        <div class="form-text">Nombre completo de quien envía.</div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="dni" class="form-label">DNI</label>
                        <input type="text" id="dni" name="dni" class="form-control" required aria-required="true" inputmode="numeric" pattern="[0-9]*">
                        <div class="form-text">Documento de identidad del remitente.</div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="correo" class="form-label">Correo del remitente</label>
                        <input type="email" id="correo" name="correo" class="form-control" placeholder="ejemplo@gmail.com">
                        <div class="form-text">Recibirás confirmación si ingresas tu correo.</div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="tipo_documento_id" class="form-label">Tipo de documento</label>
                        <select id="tipo_documento_id" name="tipo_documento_id" class="form-select" required aria-required="true">
                            <option value="">Seleccione tipo...</option>
                            @foreach ($tipos as $t)
                            <option value="{{ $t->tipo_id }}">{{ $t->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="asunto" class="form-label">Asunto</label>
                        <input type="text" id="asunto" name="asunto" class="form-control" required aria-required="true">
                    </div>

                    <div class="col-12">
                        <label for="detalle" class="form-label">Detalle</label>
                        <textarea id="detalle" name="detalle" class="form-control" rows="4" placeholder="Escribe el detalle del documento..."></textarea>
                    </div>

                    <div class="col-12">
                        <label for="archivos" class="form-label">Archivos (opcional)</label>
                        <input type="file" id="archivos" name="archivos[]" class="form-control" multiple>
                        <small class="text-muted">Formatos: pdf, docx, jpg, png. Máx 5MB c/u.</small>
                        <ul id="archivos-list" class="list-unstyled small mt-2"></ul>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary px-4">Enviar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script defer src="{{ asset('js/mesa-create.js') }}"></script>
@endpush