@extends('layouts.app')

@section('title', 'Nosotros - I.E. JFSC')
@section('body_class', 'page-nosotros waves-compact')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/nosotros.css') }}" />
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
@endpush

@section('content')
@php
  $historiaImgUrls = collect($historiaImagenes)->map(
      fn($img) => str_starts_with($img, 'images/') ? asset($img) : asset('storage/' . $img)
  );
@endphp

<!-- Hero Section -->
<section class="nosotros-hero py-3">
  <div class="container">
    <div class="row align-items-center g-4 g-lg-5">
      <div class="col-lg-5" data-aos="fade-right">
        <span class="hero-eyebrow d-inline-flex align-items-center gap-2">
          <span class="nosotros-eyebrow-rule"></span>Nuestra Esencia
        </span>
        <h1 class="nosotros-historia-title mb-4">{{ $rows['historia_titulo'] ?? 'Nuestra Historia' }}</h1>
        <p class="lead text-muted">{!! $rows['historia_p1'] ?? '' !!}</p>
        <p class="text-muted mb-0">{!! $rows['historia_p2'] ?? '' !!}</p>
      </div>
      <div class="col-lg-7" data-aos="fade-left">
        <div id="historiaCarousel" class="carousel slide nosotros-hero-carousel shadow-lg" data-bs-ride="carousel">
          <div class="carousel-inner">
            @foreach($historiaImgUrls as $i => $url)
              <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                <img src="{{ $url }}"
                     alt="Fachada histórica del colegio José Faustino Sánchez Carrión"
                     class="d-block w-100"
                     loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
              </div>
            @endforeach
          </div>
          @if($historiaImgUrls->count() > 1)
            <button class="carousel-control-prev" type="button" data-bs-target="#historiaCarousel" data-bs-slide="prev">
              <i class="bi bi-chevron-left"></i>
              <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#historiaCarousel" data-bs-slide="next">
              <i class="bi bi-chevron-right"></i>
              <span class="visually-hidden">Siguiente</span>
            </button>
          @endif
        </div>
        @if($historiaImgUrls->count() > 1)
          <div class="nosotros-carousel-dots">
            @foreach($historiaImgUrls as $i => $url)
              <button type="button" data-bs-target="#historiaCarousel" data-bs-slide-to="{{ $i }}"
                      class="{{ $i === 0 ? 'active' : '' }}" aria-label="Foto {{ $i + 1 }}"></button>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>
</section>

<!-- Misión y Visión -->
<section class="mision-vision py-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-6" data-aos="fade-up">
        <div class="card h-100 border-0 shadow-sm hover-lift">
          <div class="card-body p-4">
            <div class="icon-wrapper mb-3">
              <i class="bi bi-bullseye text-primary display-4"></i>
            </div>
            <h2 class="h3 fw-bold mb-3">Nuestra Misión</h2>
            <p class="text-muted mb-0">{{ $rows['mision'] ?? '' }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
        <div class="card h-100 border-0 shadow-sm hover-lift">
          <div class="card-body p-4">
            <div class="icon-wrapper mb-3">
              <i class="bi bi-eye text-primary display-4"></i>
            </div>
            <h2 class="h3 fw-bold mb-3">Nuestra Visión</h2>
            <p class="text-muted mb-0">{{ $rows['vision'] ?? '' }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Pilares Institucionales -->
@if(count($pilares) > 0)
<section class="pilares py-5">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <h2 class="h1 fw-bold mb-3">Nuestros Pilares</h2>
      <p class="lead text-muted">Los valores que nos definen y guían nuestro quehacer educativo</p>
    </div>
    <div class="row g-4">
      @foreach($pilares as $index => $pilar)
      <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $index * 50 }}">
        <div class="pilar-card h-100">
          <div class="pilar-icon bg-{{ $pilar['color'] }} bg-opacity-10">
            <i class="bi bi-{{ $pilar['icon'] }} text-{{ $pilar['color'] }}"></i>
          </div>
          <h3 class="h5 fw-bold mb-2">{{ $pilar['titulo'] }}</h3>
          <p class="text-muted small mb-0">{{ $pilar['desc'] }}</p>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- Normas de Convivencia -->
@if(count($normas) > 0)
<section class="normas py-5">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <h2 class="h1 fw-bold mb-3">Normas de Convivencia</h2>
      <p class="lead text-muted">Compromisos que asumimos como comunidad educativa</p>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="normas-list" data-aos="fade-up" data-aos-delay="100">
          @foreach($normas as $index => $norma)
          <div class="norma-item" data-aos="fade-left" data-aos-delay="{{ 100 + ($index * 50) }}">
            <div class="norma-number">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="norma-text">{{ $norma }}</div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>
@endif

<!-- CTA Section -->
<section class="cta-section py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 text-center" data-aos="zoom-in">
        <h2 class="h2 fw-bold mb-3">Conoce a Nuestro Equipo Directivo</h2>
        <p class="lead text-muted mb-4">
          Conoce a los profesionales comprometidos con la excelencia educativa de nuestra institución
        </p>
        <a href="{{ route('comite-directivo') }}" class="btn btn-primary btn-lg px-5">
          <i class="bi bi-people-fill me-2"></i>Ver Comité Directivo
        </a>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    AOS.init({ duration: 800, easing: 'ease-in-out', once: true, offset: 100 });
  });
</script>
@endpush
