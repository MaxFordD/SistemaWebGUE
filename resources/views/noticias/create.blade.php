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
                                <i class="bi bi-info-circle me-1"></i>Use el editor para dar formato al texto, agregar enlaces, listas, etc.
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

                        {{-- Videos de YouTube o Facebook --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-play-circle me-1"></i>Videos (YouTube o Facebook)
                            </label>
                            <div id="video-urls-container">
                                <div class="input-group mb-2 video-url-row">
                                    <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                    <input type="url" name="video_urls[]" class="form-control"
                                           placeholder="https://www.youtube.com/watch?v=... o https://www.facebook.com/...">
                                    <button type="button" class="btn btn-outline-danger btn-quitar-video d-none">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="btn-agregar-video">
                                <i class="bi bi-plus-circle me-1"></i>Agregar otro video
                            </button>
                            <div class="form-text"><i class="bi bi-info-circle me-1"></i>Pega el enlace del video de YouTube o Facebook.</div>
                        </div>

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
<script src="{{ asset('vendor/tinymce/js/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('js/tinymce-init.js') }}"></script>
<script defer src="{{ asset('js/noticia-create.js') }}"></script>
<script>
document.getElementById('btn-agregar-video').addEventListener('click', function () {
    const container = document.getElementById('video-urls-container');
    const row = document.createElement('div');
    row.className = 'input-group mb-2 video-url-row';
    row.innerHTML = `<span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
        <input type="url" name="video_urls[]" class="form-control" placeholder="https://www.youtube.com/watch?v=... o https://www.facebook.com/...">
        <button type="button" class="btn btn-outline-danger btn-quitar-video"><i class="bi bi-x-lg"></i></button>`;
    container.appendChild(row);
    row.querySelector('.btn-quitar-video').addEventListener('click', () => row.remove());
});
</script>
@endpush
