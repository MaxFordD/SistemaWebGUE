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

    <div class="card border-0 shadow-sm rounded-4 mesa-form-card">
        <div class="card-body p-3 p-md-4">
            <p class="text-muted mb-4">
                <i class="bi bi-info-circle me-2"></i>
                Complete el formulario con la información requerida. Los campos marcados con asterisco (*) son obligatorios.
            </p>
            <form action="{{ route('mesa.store') }}" method="post" enctype="multipart/form-data" novalidate id="mesaForm">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="remitente" class="form-label">
                            Remitente <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" id="remitente" name="remitente" class="form-control" required aria-required="true" placeholder="Nombre completo">
                        </div>
                        <div class="invalid-feedback">
                            Por favor, ingrese el nombre del remitente.
                        </div>
                        <div class="form-text">Nombre completo de quien envía.</div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="dni" class="form-label">
                            DNI <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                            <input type="text" id="dni" name="dni" class="form-control" required aria-required="true" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" placeholder="12345678" minlength="8">
                        </div>
                        <div class="invalid-feedback">
                            Por favor, ingrese un DNI válido de 8 dígitos.
                        </div>
                        <div class="form-text">Documento de identidad del remitente.</div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="correo" class="form-label">Correo del remitente</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" id="correo" name="correo" class="form-control" placeholder="ejemplo@gmail.com">
                        </div>
                        <div class="invalid-feedback">
                            Por favor, ingrese un correo electrónico válido.
                        </div>
                        <div class="form-text">Recibirás confirmación si ingresas tu correo.</div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="tipo_documento_id" class="form-label">
                            Tipo de documento <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-file-earmark"></i></span>
                            <select id="tipo_documento_id" name="tipo_documento_id" class="form-select" required aria-required="true">
                                <option value="">Seleccione tipo...</option>
                                @foreach ($tipos as $t)
                                <option value="{{ $t->tipo_id }}">{{ $t->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="invalid-feedback">
                            Por favor, seleccione el tipo de documento.
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="asunto" class="form-label">
                            Asunto <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-chat-left-text"></i></span>
                            <input type="text" id="asunto" name="asunto" class="form-control" required aria-required="true" placeholder="Breve descripción del asunto">
                        </div>
                        <div class="invalid-feedback">
                            Por favor, ingrese el asunto del documento.
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="detalle" class="form-label">Detalle</label>
                        <textarea id="detalle" name="detalle" class="form-control" rows="4" placeholder="Escribe el detalle del documento..."></textarea>
                    </div>

                    <div class="col-12">
                        <label for="archivos" class="form-label">
                            <i class="bi bi-paperclip me-1"></i> Archivos adjuntos (opcional)
                        </label>
                        <input type="file" id="archivos" name="archivos[]" class="form-control" multiple accept=".pdf,.docx,.jpg,.jpeg,.png">
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Formatos permitidos: PDF, DOCX, JPG, PNG. Máximo 5MB por archivo.
                        </div>
                        <div id="archivos-preview" class="mt-3"></div>
                    </div>
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-4 gap-2">
                    <div class="text-muted small">
                        <i class="bi bi-shield-check me-1"></i>
                        Sus datos están protegidos y serán procesados de forma segura.
                    </div>
                    <button type="submit" class="btn btn-primary px-5 btn-submit">
                        <i class="bi bi-send me-2"></i>Enviar Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script defer src="{{ asset('js/mesa-create.js') }}"></script>
@endpush