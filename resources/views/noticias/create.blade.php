@extends('layouts.app')

@section('title', 'Crear Noticia')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 fw-bold mb-0">
                    <i class="bi bi-newspaper me-2"></i>Crear Noticia
                </h1>
                <a href="{{ route('noticias.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong><i class="bi bi-exclamation-triangle me-2"></i>Revisa los campos:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('noticias.store') }}" method="POST" enctype="multipart/form-data" id="noticiaForm">
                        @csrf

                        <div class="mb-4">
                            <label for="titulo" class="form-label fw-semibold">
                                <i class="bi bi-text-left me-1"></i>Título <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="titulo" id="titulo" class="form-control form-control-lg"
                                   value="{{ old('titulo') }}" maxlength="200" required
                                   placeholder="Ingrese el título de la noticia">
                        </div>

                        <div class="mb-4">
                            <label for="contenido" class="form-label fw-semibold">
                                <i class="bi bi-card-text me-1"></i>Contenido <span class="text-danger">*</span>
                            </label>
                            <textarea name="contenido" id="contenido" class="form-control" rows="8" required
                                      placeholder="Escriba el contenido de la noticia...">{{ old('contenido') }}</textarea>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>Escriba el contenido completo de la noticia
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="archivos" class="form-label fw-semibold">
                                <i class="bi bi-images me-1"></i>Imágenes y Documentos
                            </label>
                            <input type="file" name="archivos[]" id="archivos" class="form-control"
                                   accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" multiple>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Puede seleccionar múltiples archivos. Imágenes: JPG, PNG, GIF. Documentos: PDF, Word, Excel. Máx. 2 MB por archivo.
                            </div>
                        </div>

                        <!-- Preview de archivos -->
                        <div id="archivos-preview" class="mb-4"></div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('noticias.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Publicar Noticia
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script defer src="{{ asset('js/noticia-create.js') }}"></script>
@endpush
