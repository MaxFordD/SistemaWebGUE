@extends('layouts.app')

@section('title', $noticia->titulo ?? 'Noticia')

@section('body_class', 'waves-compact')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/noticia-show.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/confirm-delete.js') }}"></script>
@endpush

@section('content')
@php
$tz = config('app.timezone', 'America/Lima');
@endphp

<div class="container py-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bi bi-house-door me-1"></i>Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('noticias.index') }}">Noticias</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($noticia->titulo, 30) }}</li>
        </ol>
    </nav>

    {{-- Artículo principal --}}
    <article class="noticia-detail">
        {{-- Encabezado del artículo --}}
        <header class="mb-4">
            <h1 class="display-5 fw-bold mb-3" style="color: var(--color-primary, #7a1a0c); line-height: 1.3;">
                {{ $noticia->titulo }}
            </h1>

            {{-- Meta información --}}
            <div class="d-flex flex-wrap align-items-center gap-3 text-muted border-bottom pb-3 mb-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-person-circle fs-5 me-2"></i>
                    <div>
                        <small class="text-muted d-block" style="font-size: 0.75rem;">Autor</small>
                        <strong class="text-dark">{{ $noticia->autor ?? 'Redacción GUE' }}</strong>
                    </div>
                </div>

                @if(!empty($noticia->fecha_publicacion))
                <div class="vr d-none d-md-block"></div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-calendar-event fs-5 me-2"></i>
                    <div>
                        <small class="text-muted d-block" style="font-size: 0.75rem;">Publicado</small>
                        <strong class="text-dark">{{ \Carbon\Carbon::parse($noticia->fecha_publicacion)->setTimezone($tz)->translatedFormat('d \d\e F Y') }}</strong>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-clock fs-5 me-2"></i>
                    <div>
                        <small class="text-muted d-block" style="font-size: 0.75rem;">Hora</small>
                        <strong class="text-dark">{{ \Carbon\Carbon::parse($noticia->fecha_publicacion)->setTimezone($tz)->format('H:i') }}</strong>
                    </div>
                </div>
                @endif
            </div>

            {{-- Botones de compartir mejorados --}}
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="text-muted me-2"><i class="bi bi-share me-1"></i>Compartir:</span>
                <a class="btn btn-sm btn-outline-primary rounded-pill"
                   href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}"
                   target="_blank"
                   rel="noopener"
                   aria-label="Compartir en Facebook">
                    <i class="bi bi-facebook me-1"></i>Facebook
                </a>
                <a class="btn btn-sm btn-outline-info rounded-pill"
                   href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($noticia->titulo) }}"
                   target="_blank"
                   rel="noopener"
                   aria-label="Compartir en Twitter">
                    <i class="bi bi-twitter-x me-1"></i>Twitter
                </a>
                <a class="btn btn-sm btn-outline-success rounded-pill"
                   href="https://api.whatsapp.com/send?text={{ urlencode($noticia->titulo.' '.request()->fullUrl()) }}"
                   target="_blank"
                   rel="noopener"
                   aria-label="Compartir en WhatsApp">
                    <i class="bi bi-whatsapp me-1"></i>WhatsApp
                </a>
            </div>
        </header>

        @php
        $archivos = [];
        if (!empty($noticia->imagen)) {
            $archivos = array_filter(array_map('trim', explode(';', $noticia->imagen)));
        }
        @endphp

        {{-- Galería de imágenes principal --}}
        @if(count($archivos) > 0)
        @php
        $imagenes = array_filter($archivos, function($archivo) {
            $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        });
        $documentos = array_diff($archivos, $imagenes);
        @endphp

        @if(count($imagenes) > 0)
        <div class="mb-4">
            @if(count($imagenes) === 1)
            {{-- Imagen única destacada --}}
            <div class="position-relative rounded-4 overflow-hidden shadow-lg" style="max-height: 600px; background-color: #f8f9fa;">
                <img src="{{ asset('storage/' . ltrim($imagenes[array_key_first($imagenes)], '/')) }}"
                     alt="{{ $noticia->titulo }}"
                     class="w-100"
                     style="object-fit: contain; max-height: 600px;">
            </div>
            @else
            {{-- Galería de múltiples imágenes --}}
            <div id="galeriaNoticia" class="carousel slide shadow-lg rounded-4 overflow-hidden" data-bs-ride="carousel" style="background-color: #f8f9fa;">
                <div class="carousel-indicators">
                    @foreach($imagenes as $index => $imagen)
                    <button type="button" data-bs-target="#galeriaNoticia" data-bs-slide-to="{{ $index }}"
                            class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                            aria-label="Imagen {{ $index + 1 }}"></button>
                    @endforeach
                </div>
                <div class="carousel-inner">
                    @foreach($imagenes as $index => $imagen)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ asset('storage/' . ltrim($imagen, '/')) }}"
                             class="d-block w-100"
                             alt="Imagen {{ $index + 1 }}"
                             style="max-height: 600px; object-fit: contain;">
                    </div>
                    @endforeach
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#galeriaNoticia" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#galeriaNoticia" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Siguiente</span>
                </button>
            </div>
            <p class="text-center text-muted mt-2 small">
                <i class="bi bi-images me-1"></i>{{ count($imagenes) }} {{ count($imagenes) === 1 ? 'imagen' : 'imágenes' }}
            </p>
            @endif
        </div>
        @endif
        @endif

        {{-- Contenido del artículo --}}
        <section class="bg-white p-4 p-md-5 rounded-4 shadow-sm mb-4">
            <div class="noticia-contenido" style="line-height: 1.9; font-size: 1.1rem; color: #333;">
                {!! $noticia->contenido !!}
            </div>
        </section>

        {{-- Documentos adjuntos --}}
        @if(isset($documentos) && count($documentos) > 0)
        <section class="mb-4">
            <div class="bg-light p-4 rounded-4 border">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-file-earmark-text me-2 text-primary"></i>Documentos Adjuntos
                </h5>
                <div class="row g-3">
                    @foreach($documentos as $doc)
                    @php
                    $extension = strtolower(pathinfo($doc, PATHINFO_EXTENSION));
                    $rutaCompleta = asset('storage/' . ltrim($doc, '/'));
                    $nombreArchivo = basename($doc);
                    @endphp
                    <div class="col-md-6">
                        <a href="{{ $rutaCompleta }}" target="_blank" class="text-decoration-none">
                            <div class="card h-100 border-0 shadow-sm hover-shadow-lg transition">
                                <div class="card-body d-flex align-items-center">
                                    <div class="me-3">
                                        @if($extension === 'pdf')
                                        <i class="bi bi-file-pdf fs-1 text-danger"></i>
                                        @elseif(in_array($extension, ['doc', 'docx']))
                                        <i class="bi bi-file-word fs-1 text-primary"></i>
                                        @elseif(in_array($extension, ['xls', 'xlsx']))
                                        <i class="bi bi-file-excel fs-1 text-success"></i>
                                        @else
                                        <i class="bi bi-file-earmark fs-1 text-secondary"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 text-dark">{{ $nombreArchivo }}</h6>
                                        <small class="text-muted">{{ strtoupper($extension) }}</small>
                                    </div>
                                    <div>
                                        <i class="bi bi-download text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        {{-- Miniaturas de galería (si hay múltiples imágenes) --}}
        @if(isset($imagenes) && count($imagenes) > 1)
        <section class="mb-4">
            <h5 class="fw-bold mb-3">
                <i class="bi bi-images me-2"></i>Galería de Imágenes
            </h5>
            <div class="row g-3">
                @foreach($imagenes as $index => $imagen)
                <div class="col-6 col-md-3">
                    <a href="{{ asset('storage/' . ltrim($imagen, '/')) }}" target="_blank" class="d-block">
                        <div class="position-relative rounded-3 overflow-hidden shadow-sm hover-shadow-lg transition" style="aspect-ratio: 1/1;">
                            <img src="{{ asset('storage/' . ltrim($imagen, '/')) }}"
                                 alt="Miniatura {{ $index + 1 }}"
                                 class="w-100 h-100"
                                 style="object-fit: cover;">
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-dark bg-opacity-75">
                                    <i class="bi bi-zoom-in"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Navegación y Acciones --}}
        <footer class="mt-5 pt-4 border-top">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <a href="{{ route('noticias.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i>Volver a noticias
                </a>

                <div class="d-flex gap-2 align-items-center">
                    <div class="text-muted small me-3">
                        <i class="bi bi-eye me-1"></i>Noticia #{{ $noticia->noticia_id }}
                    </div>

                    @auth
                    @php
                        $user = auth()->user();
                        $rolesUsuario = [];
                        try {
                            $rolesUsuario = collect(DB::select('EXEC sp_UsuarioRol_ListarPorUsuario ?', [$user->usuario_id ?? $user->id]))
                                ->pluck('nombre_rol')
                                ->map(fn($r) => strtolower(trim($r)))
                                ->toArray();
                        } catch (\Exception $e) {}

                        $tienePermiso = !empty(array_intersect($rolesUsuario, ['editor', 'administrador', 'director']));
                    @endphp

                    @if($tienePermiso)
                    <a href="{{ route('noticias.edit', $noticia->noticia_id) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil-square me-1"></i>Editar
                    </a>
                    <form action="{{ route('noticias.destroy', $noticia->noticia_id) }}" method="POST" class="d-inline delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash me-1"></i>Eliminar
                        </button>
                    </form>
                    @endif
                    @endauth
                </div>
            </div>
        </footer>
    </article>
</div>
@endsection
