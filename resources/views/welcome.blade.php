@extends('layouts.app')

@section('title','Inicio')
@section('body_class','page-home')

@push('styles')
<style>
.modal-close-top-right{ right:.5rem; top:.5rem; }
/* Subir el hero (carrusel + texto) solo en la página de inicio */
body.page-home .main-wrapper > .container.mt-3{ margin-top:.25rem !important; }
body.page-home #main-content{ margin-top:0 !important; }
</style>
@endpush

@section('content')
{{-- ====== HERO 60/40 (Fotografía + Contenido) ====== --}}
<section id="hero" class="hero-split pt-0 pt-lg-1 pb-4 pb-lg-5">
  <div class="container">
    <div class="row g-3 g-lg-4 align-items-stretch">
      {{-- Columna IZQUIERDA (60%): Carrusel institucional --}}
      <div class="col-12 col-lg-7">
        <div id="heroCarousel" class="carousel slide carousel-fade h-100 rounded-3 overflow-hidden shadow-sm"
             data-bs-ride="carousel" data-bs-interval="5000">
          <div class="carousel-inner h-100">
            @foreach($slides as $i => $s)
              <div class="carousel-item h-100 {{ $i === 0 ? 'active' : '' }}">
                @if($i === 0)
                <img src="{{ asset($s->ruta) }}" class="d-block w-100 h-100 object-cover"
                     alt="{{ $s->alt }}" loading="eager" fetchpriority="high" decoding="async">
                @else
                <img src="{{ asset($s->ruta) }}" class="d-block w-100 h-100 object-cover"
                     alt="{{ $s->alt }}" loading="lazy" decoding="async">
                @endif
              </div>
            @endforeach
          </div>

          {{-- Controles --}}
          <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" aria-label="Anterior">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" aria-label="Siguiente">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
          </button>

          {{-- Indicadores --}}
          <div class="carousel-indicators">
            @foreach($slides as $i => $s)
              <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $i }}"
                      class="{{ $i === 0 ? 'active' : '' }}"
                      aria-current="{{ $i === 0 ? 'true' : 'false' }}"
                      aria-label="Diapositiva {{ $i + 1 }}"></button>
            @endforeach
          </div>
        </div>
      </div>

      {{-- Columna DERECHA (40%): Texto + botones --}}
      <div class="col-12 col-lg-5">
        <div class="hero-copy h-100 rounded-3 shadow-sm d-flex flex-column justify-content-center p-4 p-lg-5">

          <span class="hero-eyebrow">Institución Educativa Emblemática</span>

          <h1 class="fw-bold mb-3 lh-sm">
            José Faustino Sánchez Carrión
          </h1>

          <p class="lead text-muted mb-4">
            Donde la educación es la base para el futuro. Formamos estudiantes con excelencia académica,
            valores y compromiso ciudadano.
          </p>

          <div class="mt-auto pt-2">
            <a href="{{ route('nosotros') }}" class="btn btn-primary btn-lg me-3 mb-2">Conocer más</a>
            <a href="{{ route('noticias.index') }}" class="btn btn-outline-secondary btn-lg mb-2">Ver noticias</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

  {{-- ====== LOGROS INSTITUCIONALES ====== --}}
  <section class="logros-section py-5">
    <div class="container">
      <div class="section-heading text-center mb-5">
        <h2 class="h1 fw-bold mb-0">Logros Institucionales</h2>
      </div>
      <div class="row g-4">
        @foreach($logros as $l)
          <div class="col-md-6 col-lg-3 animate-on-scroll">
            <div class="logro-card h-100">
              <div class="logro-icon"><i class="bi {{ $l['icon'] }}"></i></div>
              <h5 class="fw-bold mb-2">{{ $l['titulo'] }}</h5>
              <p class="text-muted mb-0 small">{{ $l['descripcion'] }}</p>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ====== TALLERES / ACTIVIDADES ====== --}}
  <section class="talleres py-5">
    <div class="container">
      <div class="section-heading text-center mb-5">
        <h2 class="h1 fw-bold mb-0">Talleres y Actividades</h2>
      </div>

      <div class="row g-4">
        @foreach($talleres as $t)
          <div class="col-md-6 col-lg-3 animate-on-scroll">
            <div class="card h-100 shadow-sm border-0 hover-lift taller-card">
              <div class="card-img-wrapper">
                <img class="card-img-top"
                     src="{{ asset($t->ruta) }}"
                     alt="{{ $t->alt }}"
                     loading="lazy" decoding="async">
                <div class="card-overlay">
                  <i class="bi bi-{{ $t->icono ?? 'star-fill' }} display-4 text-white"></i>
                </div>
              </div>
              <div class="card-body">
                <h5 class="card-title fw-bold">{{ $t->titulo }}</h5>
                <p class="card-text text-muted">{{ $t->descripcion }}</p>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

{{-- ====== ÚLTIMAS NOTICIAS ====== --}}
  @if($ultimas->isNotEmpty())
  <section class="noticias-home-section">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="section-heading mb-0">
          <h2 class="h1 fw-bold mb-0">Últimas Noticias</h2>
        </div>
        <a href="{{ route('noticias.index') }}" class="btn btn-outline-primary btn-sm">Ver todas</a>
      </div>
      <div class="row g-4">
        @foreach($ultimas as $n)
        <div class="col-md-4 animate-on-scroll">
          @include('noticias._card', ['noticia' => $n])
        </div>
        @endforeach
      </div>
    </div>
  </section>
  @endif

@endsection

@push('scripts')
<script defer src="{{ asset('js/welcome.js') }}"></script>
@endpush
