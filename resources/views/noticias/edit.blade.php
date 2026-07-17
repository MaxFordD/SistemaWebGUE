@extends('layouts.app')

@section('title', 'Editar Noticia')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 fw-bold mb-0">
                    <i class="bi bi-pencil-square me-2"></i>Editar Noticia
                </h1>
                <a href="{{ route('noticias.show', $noticia->noticia_id) }}" class="btn btn-outline-secondary">
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
                    <form action="{{ route('noticias.update', $noticia->noticia_id) }}" method="POST" enctype="multipart/form-data" id="noticiaForm">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="titulo" class="form-label fw-semibold">
                                <i class="bi bi-text-left me-1"></i>Título <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="titulo" id="titulo" class="form-control form-control-lg"
                                   value="{{ old('titulo', $noticia->titulo) }}" maxlength="200" required
                                   placeholder="Ingrese el título de la noticia">
                        </div>

                        <div class="mb-4">
                            <label for="contenido" class="form-label fw-semibold">
                                <i class="bi bi-card-text me-1"></i>Contenido <span class="text-danger">*</span>
                            </label>
                            <textarea name="contenido" id="contenido" class="form-control" rows="8" required
                                      placeholder="Escriba el contenido de la noticia...">{{ old('contenido', $noticia->contenido) }}</textarea>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>Use el editor para dar formato al texto, agregar enlaces, listas, etc.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="estado" class="form-label fw-semibold">
                                <i class="bi bi-toggle-on me-1"></i>Estado <span class="text-danger">*</span>
                            </label>
                            <select name="estado" id="estado" class="form-select" required>
                                <option value="A" {{ old('estado', $noticia->estado) == 'A' ? 'selected' : '' }}>Activo (Publicado)</option>
                                <option value="I" {{ old('estado', $noticia->estado) == 'I' ? 'selected' : '' }}>Inactivo (Borrador)</option>
                            </select>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>Solo las noticias activas se muestran públicamente
                            </div>
                        </div>

                        @php
                        $archivosActuales = [];
                        $videosActuales   = [];
                        if (!empty($noticia->imagen)) {
                            foreach (array_filter(array_map('trim', explode(';', $noticia->imagen))) as $item) {
                                if (str_starts_with($item, 'http')) {
                                    $videosActuales[] = $item;
                                } else {
                                    $archivosActuales[] = $item;
                                }
                            }
                        }
                        @endphp

                        @if(count($archivosActuales) > 0)
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-paperclip me-1"></i>Archivos Actuales
                            </label>
                            <div class="row g-2">
                                @foreach($archivosActuales as $archivo)
                                @php
                                $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
                                $esImagen = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                $nombreArchivo = basename($archivo);
                                @endphp
                                <div class="col-md-4">
                                    <div class="card border">
                                        @if($esImagen)
                                        <img src="{{ asset('storage/' . ltrim($archivo, '/')) }}"
                                             class="card-img-top"
                                             alt="{{ $nombreArchivo }}"
                                             style="height: 150px; object-fit: cover;">
                                        @else
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-earmark fs-1 text-muted"></i>
                                            <p class="small mb-0 mt-2">{{ $nombreArchivo }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>Los archivos actuales se mantendrán. Puedes agregar nuevos archivos abajo.
                            </div>
                        </div>
                        @endif

                        {{-- Videos actuales --}}
                        @if(count($videosActuales) > 0)
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-play-circle me-1"></i>Videos actuales
                            </label>
                            <ul class="list-group">
                                @foreach($videosActuales as $vurl)
                                @php
                                    $plat = str_contains($vurl,'youtube.com')||str_contains($vurl,'youtu.be') ? 'youtube' : (str_contains($vurl,'facebook')||str_contains($vurl,'fb.watch') ? 'facebook' : 'otro');
                                @endphp
                                <li class="list-group-item d-flex align-items-center gap-2">
                                    <i class="bi bi-{{ $plat === 'youtube' ? 'youtube text-danger' : ($plat === 'facebook' ? 'facebook text-primary' : 'play-circle') }} fs-5"></i>
                                    <span class="text-truncate small flex-grow-1">{{ $vurl }}</span>
                                </li>
                                @endforeach
                            </ul>
                            <div class="form-text"><i class="bi bi-info-circle me-1"></i>Los videos se conservan al actualizar. Agrega nuevos abajo.</div>
                        </div>
                        @endif

                        <div class="mb-4">
                            <label for="archivos" class="form-label fw-semibold">
                                <i class="bi bi-images me-1"></i>Agregar Nuevas Imágenes y Documentos
                            </label>
                            <input type="file" name="archivos[]" id="archivos" class="form-control"
                                   accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" multiple>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Puede seleccionar múltiples archivos. Imágenes: JPG, PNG, GIF. Documentos: PDF, Word, Excel. Máx. 2 MB por archivo.
                            </div>
                        </div>

                        <!-- Preview de archivos nuevos -->
                        <div id="archivos-preview" class="mb-4"></div>

                        {{-- Agregar videos nuevos --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-play-circle me-1"></i>Agregar videos (YouTube o Facebook)
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
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('noticias.show', $noticia->noticia_id) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Actualizar Noticia
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
