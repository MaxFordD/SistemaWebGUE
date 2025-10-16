@extends('layouts.app')

@section('title', $noticia->titulo ?? 'Noticia')
@section('body_class', 'waves-compact')

@section('content')
@php($tz = config('app.timezone', 'America/Lima'))

<div class="container py-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('noticias.index') }}">Noticias</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detalle</li>
        </ol>
    </nav>

    {{-- Título --}}
    <h1 class="h3 fw-bold mb-2">{{ $noticia->titulo }}</h1>

    {{-- Meta autor/fecha y compartir --}}
    <div class="d-flex flex-wrap align-items-center gap-3 text-muted mb-4 article-meta">
        <div class="d-inline-flex align-items-center gap-2">
            <i class="bi bi-person-circle"></i>
            <span>{{ $noticia->autor ?? 'Redacción GUE' }}</span>
        </div>
        @if(!empty($noticia->fecha_publicacion))
            <div class="d-inline-flex align-items-center gap-2">
                <i class="bi bi-calendar-event"></i>
                <span>{{ \Carbon\Carbon::parse($noticia->fecha_publicacion)->setTimezone($tz)->translatedFormat('d \\d\\e F Y, H:i') }}</span>
            </div>
        @endif
        <div class="ms-auto"></div>
        <ul class="share-list list-unstyled d-flex align-items-center gap-2 mb-0">
            <li class="text-muted small me-1">Compartir:</li>
            <li><a class="btn btn-sm btn-outline-primary" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" rel="noopener">Facebook</a></li>
            <li><a class="btn btn-sm btn-outline-primary" href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($noticia->titulo) }}" target="_blank" rel="noopener">X</a></li>
            <li><a class="btn btn-sm btn-outline-primary" href="https://api.whatsapp.com/send?text={{ urlencode($noticia->titulo.' '.request()->fullUrl()) }}" target="_blank" rel="noopener">WhatsApp</a></li>
        </ul>
    </div>

    {{-- Contenido --}}
    <article class="bg-white p-4 rounded-4 shadow-sm article-content mb-3">
        <div class="content">
            {!! nl2br(e($noticia->contenido)) !!}
        </div>
    </article>

    {{-- Imagen debajo del texto (no estirada) --}}
    @if (!empty($noticia->imagen))
        <figure class="article-image mt-3 mb-4">
            <img 
                src="{{ asset('storage/' . ltrim($noticia->imagen, '/')) }}" 
                alt="Imagen de la noticia" 
                class="img-fluid rounded-4 shadow-sm"
            >
        </figure>
    @endif

    {{-- Volver --}}
    <div class="d-flex justify-content-between align-items-center mt-3">
        <a href="{{ route('noticias.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left-short"></i> Volver al listado
        </a>
    </div>
</div>
@endsection
