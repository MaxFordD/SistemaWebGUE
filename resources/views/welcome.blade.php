@extends('layouts.app')

@section('title','Inicio')
@section('body_class','page-home')

@push('styles')
<style>
.modal-close-top-right{ right:.5rem; top:.5rem; }
</style>
@endpush

@section('content')
{{-- ====== HERO en dos columnas (Carrusel + Texto) ====== --}}
<section id="hero" class="hero-split py-3">
  <div class="container">
    <div class="row g-3 g-lg-4 align-items-stretch">
      {{-- Columna IZQUIERDA: Carrusel --}}
      <div class="col-12 col-lg-6">
        <div id="heroCarousel" class="carousel slide carousel-fade h-100 rounded-3 overflow-hidden shadow-sm"
             data-bs-ride="carousel" data-bs-interval="5000">
          <div class="carousel-inner h-100">
            @php
              $slides = [
                ['img'=>'images/gue.jpg','alt'=>'Fachada del colegio'],
                ['img'=>'images/colegio001.jpg','alt'=>'Estudiantes en actividades'],
                ['img'=>'images/colegio003.jpg','alt'=>'Instalaciones del campus'],
              ];
            @endphp

            @foreach($slides as $i => $s)
              <div class="carousel-item h-100 {{ $i === 0 ? 'active' : '' }}">
                <img src="{{ asset($s['img']) }}" class="d-block w-100 h-100 object-cover"
                     alt="{{ $s['alt'] }}" loading="lazy" decoding="async">
              </div>
            @endforeach
          </div>

          {{-- Controles mejorados --}}
          <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" aria-label="Anterior">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" aria-label="Siguiente">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
          </button>

          {{-- Indicadores mejorados --}}
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

      {{-- Columna DERECHA: Texto + botones --}}
      <div class="col-12 col-lg-6">
        <div class="hero-copy h-100 rounded-3 shadow-sm d-flex flex-column justify-content-center p-4 p-lg-5">

          <h1 class="fw-bold mb-3 lh-sm">
            Institución Educativa Emblemática<br class="d-none d-xl-inline">
            José Faustino Sánchez Carrión Trujillo
          </h1>

          <p class="lead text-muted mb-4">
            Donde la educación es la base para el futuro. Formamos estudiantes con excelencia académica,
            valores y compromiso ciudadano.
          </p>

          <div class="mt-auto pt-2">
            <a href="{{ route('noticias.index') }}" class="btn btn-primary btn-lg me-3 mb-2">Ver Noticias</a>
            <a href="{{ route('nosotros') }}" class="btn btn-outline-secondary btn-lg mb-2">Conócenos</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

  {{-- ====== TALLERES / ACTIVIDADES ====== --}}
  <section class="talleres py-5 bg-white">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="h1 fw-bold mb-0">Talleres y Actividades</h2>
      </div>

      <div class="row">
        @php
          $talleres = [
            ['img'=>'images/talleres/musica.jpg','titulo'=>'Música','desc'=>'Práctica instrumental, ensambles y teoría musical.','icon'=>'music-note-beamed'],
            ['img'=>'images/talleres/deporte.jpg','titulo'=>'Deporte','desc'=>'Fútbol, vóley y atletismo para todas las categorías.','icon'=>'trophy'],
            ['img'=>'images/talleres/pintura.jpg','titulo'=>'Artes Plásticas','desc'=>'Dibujo, pintura y técnicas mixtas.','icon'=>'palette'],
            ['img'=>'images/talleres/danza.jpg','titulo'=>'Danza','desc'=>'Danza moderna y folclore peruano.','icon'=>'person-arms-up'],
          ];
        @endphp

        @foreach($talleres as $t)
          <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm border-0 hover-lift taller-card">
              <div class="card-img-wrapper">
                <img class="card-img-top"
                     src="{{ asset($t['img']) }}"
                     alt="Taller de {{ $t['titulo'] }}"
                     loading="lazy" decoding="async">
                <div class="card-overlay">
                  <i class="bi bi-{{ $t['icon'] ?? 'star-fill' }} display-4 text-white"></i>
                </div>
              </div>
              <div class="card-body">
                <h5 class="card-title fw-bold">{{ $t['titulo'] }}</h5>
                <p class="card-text text-muted">{{ $t['desc'] }}</p>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <div class="text-center d-md-none mt-2">
        <a href="#" class="btn btn-outline-dark btn-sm">Ver todos</a>
      </div>
    </div>
  </section>
 <!--
  {{-- ====== GALERÍA DE INSTALACIONES ====== --}}
  <section class="galeria py-5">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="h1 fw-bold mb-0">Galería de Instalaciones</h2>
        <small class="text-muted d-none d-md-inline">Explora nuestros ambientes</small>
      </div>

      @php
        $fotos = [
          'images/galeria/aula.jpg',
          'images/galeria/laboratorio.jpg',
          'images/galeria/biblioteca.jpg',
          'images/galeria/cancha.jpg',
          'images/galeria/patio.jpg',
          'images/galeria/auditorio.jpg',
          'images/galeria/ingreso.jpg',
          'images/galeria/computo.jpg',
        ];
      @endphp

      <div class="row g-0">
        @foreach($fotos as $i => $src)
          <div class="col-6 col-md-3">
            <a href="{{ asset($src) }}" class="gal-item d-block" data-index="{{ $i }}"
               data-bs-toggle="modal" data-bs-target="#galeriaModal">
              <img src="{{ asset($src) }}" class="img-fluid"
                   alt="Instalaciones del colegio: imagen {{ $i+1 }}"
                   loading="lazy" decoding="async">
            </a>
          </div>
        @endforeach
      </div>
    </div>
  </section>
 Modal Galería 
  <div class="modal fade" id="galeriaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content bg-dark text-white position-relative">
        <button type="button" class="btn-close btn-close-white position-absolute modal-close-top-right" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        <img id="galeriaImg" src="" class="img-fluid w-100" alt="Vista ampliada de la imagen seleccionada">
      </div>
    </div>
  </div>
</div>-->
@endsection

@push('scripts')
<script defer src="{{ asset('js/welcome.js') }}"></script>
@endpush
