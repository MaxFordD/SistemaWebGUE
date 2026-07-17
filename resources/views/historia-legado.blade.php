@extends('layouts.app')

@section('title', 'Historia y Legado - I.E. JFSC')
@section('body_class', 'page-historia-legado')

@push('styles')
<style>
/* ── Separadores de sección ── */
.hl-divider {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 2rem 0 1rem;
    color: #7a1a0c;
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2.5px;
}
.hl-divider::before,
.hl-divider::after {
    content: ''; flex: 1; height: 1px;
    background: linear-gradient(90deg, rgba(122,26,12,.25), #e0e0e0);
}
.hl-divider::after { background: linear-gradient(90deg, #e0e0e0, rgba(122,26,12,.25)); }

/* ── Layout principal: carrusel + textos lado a lado ── */
.hl-main-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.1rem;
    align-items: start;
}
@media (max-width: 767px) { .hl-main-row { grid-template-columns: 1fr; } }

/* ── Carrusel de fotos ── */
.hl-carousel-wrap {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 3px 18px rgba(122,26,12,.13);
    border-top: 3px solid #7a1a0c;
}
.hl-carousel-wrap .carousel-item img {
    width: 100%;
    height: 310px;
    object-fit: cover;
}
.hl-carousel-wrap .carousel-caption {
    background: linear-gradient(0deg, rgba(0,0,0,.7) 0%, transparent 100%);
    left: 0; right: 0; bottom: 0;
    padding: 28px 16px 14px;
    text-align: left;
}
.hl-carousel-wrap .carousel-caption h6 {
    font-size: .92rem; font-weight: 700; margin-bottom: 2px;
}
.hl-carousel-wrap .carousel-caption p {
    font-size: .78rem; opacity: .85; margin: 0; line-height: 1.4;
}
.hl-carousel-wrap .carousel-control-prev,
.hl-carousel-wrap .carousel-control-next {
    width: 36px;
    opacity: .7;
}
.hl-carousel-wrap .carousel-indicators [data-bs-target] {
    width: 6px; height: 6px; border-radius: 50%;
    background: rgba(255,255,255,.6);
    border: none;
}
.hl-carousel-wrap .carousel-indicators .active { background: #f7ca19; }

/* Foto única (sin carrusel) */
.hl-single-foto {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 3px 18px rgba(122,26,12,.13);
    border-top: 3px solid #7a1a0c;
    cursor: pointer;
}
.hl-single-foto img {
    width: 100%; height: 310px;
    object-fit: cover;
    display: block;
    transition: transform .35s;
}
.hl-single-foto:hover img { transform: scale(1.03); }
.hl-single-foto-cap {
    background: #fff;
    padding: .7rem 1rem .8rem;
    border-top: 1px solid #f0f0f0;
}
.hl-single-foto-cap h6 { font-weight: 700; font-size: .9rem; margin-bottom: 2px; color: #212529; }
.hl-single-foto-cap p  { font-size: .8rem; color: #6c757d; margin: 0; line-height: 1.5; }

/* ── Panel de textos (columna derecha) ── */
.hl-textos-col { display: flex; flex-direction: column; gap: .75rem; }
.hl-texto-card {
    background: #fff;
    border-radius: 10px;
    padding: 1.25rem 1.25rem 1.1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,.07);
    border-top: 3px solid #7a1a0c;
    position: relative;
}
.hl-texto-card h6 {
    font-weight: 700; font-size: .92rem;
    margin-bottom: .45rem; color: #7a1a0c;
}
.hl-texto-card p {
    color: #444; line-height: 1.75; margin: 0;
    font-size: .88rem;
    max-height: 200px;
    overflow-y: auto;
}
.hl-texto-card p::-webkit-scrollbar { width: 4px; }
.hl-texto-card p::-webkit-scrollbar-thumb { background: rgba(122,26,12,.2); border-radius: 4px; }

/* ── Videos: grid 2 col ── */
.hl-videos-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 767px) { .hl-videos-grid { grid-template-columns: 1fr; } }

.hl-video-card {
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(122,26,12,.1);
    border-top: 3px solid #7a1a0c;
}
.hl-video-embed {
    position: relative; padding-bottom: 56.25%; height: 0; background: #1a0504;
}
.hl-video-embed iframe {
    position: absolute; inset: 0; width: 100%; height: 100%; border: none;
}
.hl-video-body { padding: .75rem 1rem .85rem; border-top: 1px solid #f0f0f0; }
.hl-video-body h6 {
    font-weight: 700; font-size: .9rem; margin-bottom: 2px;
    display: flex; align-items: center; gap: 6px; color: #212529;
}
.hl-video-body h6 .bi { color: #7a1a0c; }
.hl-video-body p { color: #6c757d; font-size: .8rem; margin: 0; line-height: 1.5; }

/* ── Lightbox ── */
#hl-lightbox {
    display: none; position: fixed; inset: 0;
    background: rgba(20,4,2,.92);
    z-index: 9999; align-items: center; justify-content: center; flex-direction: column;
}
#hl-lightbox.active { display: flex; }
#hl-lightbox img {
    max-width: 92vw; max-height: 82vh;
    border-radius: 8px; object-fit: contain;
    box-shadow: 0 8px 48px rgba(0,0,0,.6);
    border: 2px solid rgba(247,202,25,.2);
}
#hl-lb-close {
    position: fixed; top: 16px; right: 22px;
    color: #f7ca19; font-size: 2rem; cursor: pointer; line-height: 1; opacity: .85;
}
#hl-lb-close:hover { opacity: 1; }
#hl-lb-caption {
    margin-top: 10px; color: rgba(255,255,255,.8);
    font-size: .84rem; text-align: center; max-width: 580px; padding: 0 16px;
}

/* ── Vacío ── */
.hl-empty { text-align: center; padding: 60px 0; color: #adb5bd; }
.hl-empty i { font-size: 3.5rem; display: block; margin-bottom: .8rem; color: rgba(122,26,12,.2); }
</style>
@endpush

@section('content')

<div class="container pt-1">
    <div class="section-heading text-center mb-4">
        <h1 class="h1 fw-bold mb-2">Historia y Legado</h1>
        <p class="lead text-muted mb-0">Memoria viva de la I.E. José Faustino Sánchez Carrión</p>
    </div>
</div>

<div class="container py-4">
@php
    $activos = $items->where('estado','A')->sortBy('orden');
    $fotos   = $activos->where('tipo','foto')->values();
    $textos  = $activos->where('tipo','texto')->values();
    $videos  = $activos->where('tipo','video')->values();
    $hayFotos  = $fotos->isNotEmpty();
    $hayTextos = $textos->isNotEmpty();
    $hayVideos = $videos->isNotEmpty();
@endphp

@if($activos->isEmpty())
    <div class="hl-empty">
        <i class="bi bi-hourglass-split"></i>
        <p class="fw-semibold mb-1">Próximamente</p>
        <small>Aquí aparecerá el contenido de Historia y Legado.</small>
    </div>
@else

    {{-- ══ BLOQUE 1: Fotos + Textos lado a lado ══ --}}
    @if($hayFotos || $hayTextos)


    <div class="{{ ($hayFotos && $hayTextos) ? 'hl-main-row' : '' }}">

        {{-- Columna de fotos --}}
        @if($hayFotos)
        <div>
            @if($fotos->count() === 1)
                @php $f = $fotos->first() @endphp
                <div class="hl-single-foto"
                     onclick="abrirLb('{{ asset('storage/'.$f->archivo) }}','{{ addslashes($f->titulo) }}','{{ addslashes($f->contenido ?? '') }}')">
                    <img src="{{ asset('storage/'.$f->archivo) }}" alt="{{ $f->titulo }}" loading="lazy">
                    @if($f->titulo || $f->contenido)
                    <div class="hl-single-foto-cap">
                        @if($f->titulo)<h6>{{ $f->titulo }}</h6>@endif
                        @if($f->contenido && !$hayTextos)<p>{{ $f->contenido }}</p>@endif
                    </div>
                    @endif
                </div>
            @else
                <div id="fotosCarousel" class="carousel slide hl-carousel-wrap" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        @foreach($fotos as $i => $f)
                        <button type="button" data-bs-target="#fotosCarousel"
                                data-bs-slide-to="{{ $i }}"
                                {{ $i === 0 ? 'class=active aria-current=true' : '' }}
                                aria-label="Foto {{ $i+1 }}"></button>
                        @endforeach
                    </div>
                    <div class="carousel-inner">
                        @foreach($fotos as $i => $f)
                        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}"
                             style="cursor:pointer"
                             onclick="abrirLb('{{ asset('storage/'.$f->archivo) }}','{{ addslashes($f->titulo) }}','{{ addslashes($f->contenido ?? '') }}')">
                            <img src="{{ asset('storage/'.$f->archivo) }}" alt="{{ $f->titulo }}" loading="{{ $i===0?'eager':'lazy' }}">
                            @if($f->titulo || $f->contenido)
                            <div class="carousel-caption">
                                @if($f->titulo)<h6>{{ $f->titulo }}</h6>@endif
                                @if($f->contenido && !$hayTextos)<p>{{ Str::limit($f->contenido, 100) }}</p>@endif
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @if($fotos->count() > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#fotosCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#fotosCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                    @endif
                </div>
            @endif
        </div>
        @endif

        {{-- Columna de textos --}}
        @if($hayTextos)
        <div class="hl-textos-col">
            @foreach($textos as $t)
            <div class="hl-texto-card">
                <h6>{{ $t->titulo }}</h6>
                <p>{!! nl2br(e($t->contenido)) !!}</p>
            </div>
            @endforeach
        </div>
        @endif

    </div>
    @endif

    {{-- ══ BLOQUE 2: Videos ══ --}}
    @if($hayVideos)
    <div class="hl-videos-grid mt-4">
        @foreach($videos as $v)
        <div class="hl-video-card">
            <div class="hl-video-embed">
                <iframe src="{{ $v->url_video }}"
                        title="{{ $v->titulo }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
            </div>
            <div class="hl-video-body">
                <h6><i class="bi bi-play-circle-fill"></i>{{ $v->titulo }}</h6>
                @if($v->contenido)<p>{{ $v->contenido }}</p>@endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

@endif
</div>

{{-- Lightbox --}}
<div id="hl-lightbox" onclick="cerrarLb()">
    <span id="hl-lb-close" onclick="cerrarLb()">&times;</span>
    <img id="hl-lb-img" src="" alt="">
    <div id="hl-lb-caption"></div>
</div>
@endsection

@push('scripts')
<script>
function abrirLb(src, titulo, desc) {
    document.getElementById('hl-lb-img').src = src;
    document.getElementById('hl-lb-img').alt = titulo;
    document.getElementById('hl-lb-caption').textContent = titulo + (desc ? ' — ' + desc : '');
    document.getElementById('hl-lightbox').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function cerrarLb() {
    document.getElementById('hl-lightbox').classList.remove('active');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarLb(); });
</script>
@endpush
