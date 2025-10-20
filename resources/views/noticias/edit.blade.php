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
                        if (!empty($noticia->imagen)) {
                            $archivosActuales = array_filter(array_map('trim', explode(';', $noticia->imagen)));
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
@endpush
