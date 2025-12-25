@extends('layouts.app')

@section('title', 'Comité Directivo - I.E. JFSC')
@section('body_class', 'page-comite-directivo')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/comite-directivo.css') }}" />
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
@endpush

@section('content')

<section class="directivo-hero py-5 bg-light">
  <div class="container">
    <div class="text-center" data-aos="fade-up">
      <h1 class="display-4 fw-bold mb-3">Comité Directivo</h1>
      <p class="lead text-muted mb-0">
        Conoce al equipo de profesionales comprometidos con la excelencia educativa de nuestra institución
      </p>
    </div>
  </div>
</section>


<section class="filtros py-3 bg-white border-bottom">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-center gap-2" data-aos="fade-up">

      <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="general">
        Gestión General
      </button>
    </div>
  </div>
</section>


<section class="directivos-grid py-5">
  <div class="container">
    @if($directivos->isEmpty())
      <div class="alert alert-info text-center" role="alert">
        <i class="bi bi-info-circle me-2"></i>
        No hay directivos registrados en este momento.
      </div>
    @else
      <div class="row g-4" id="directivosContainer">
        @foreach($directivos as $index => $directivo)
          @php
            // Determinar el filtro de grado basado en grado_cargo
            $filtroGrado = 'general';
            if (stripos($directivo->grado_cargo, '1°') !== false || stripos($directivo->grado_cargo, 'primer') !== false) {
              $filtroGrado = '1';
            } elseif (stripos($directivo->grado_cargo, '2°') !== false || stripos($directivo->grado_cargo, 'segundo') !== false) {
              $filtroGrado = '2';
            } elseif (stripos($directivo->grado_cargo, '3°') !== false || stripos($directivo->grado_cargo, 'tercer') !== false) {
              $filtroGrado = '3';
            } elseif (stripos($directivo->grado_cargo, '4°') !== false || stripos($directivo->grado_cargo, 'cuarto') !== false) {
              $filtroGrado = '4';
            } elseif (stripos($directivo->grado_cargo, '5°') !== false || stripos($directivo->grado_cargo, 'quinto') !== false) {
              $filtroGrado = '5';
            }

            // Detectar si es un cargo pendiente
            $isPendiente = stripos($directivo->nombre_completo, 'pendiente') !== false;
          @endphp

          <div class="col-sm-6 col-md-4 col-lg-3 directivo-item"
               data-grado="{{ $filtroGrado }}"
               data-aos="fade-up"
               data-aos-delay="{{ $index * 50 }}">
            <div class="directivo-card h-100 {{ $isPendiente ? 'is-pendiente' : '' }}">
              <div class="directivo-foto">
                @if($directivo->foto && !$isPendiente)
                  <img src="{{ asset('storage/' . $directivo->foto) }}"
                       alt="Foto de {{ $directivo->nombre_completo }}"
                       class="img-fluid">
                @else
                  <div class="directivo-avatar {{ $isPendiente ? 'avatar-pendiente' : '' }}">
                    @if($isPendiente)
                      <i class="bi bi-hourglass-split"></i>
                    @else
                      <i class="bi bi-person-circle"></i>
                    @endif
                  </div>
                @endif
              </div>

              <div class="directivo-info">
                <h3 class="directivo-nombre {{ $isPendiente ? 'text-muted' : '' }}">
                  {{ $directivo->nombre_completo }}
                </h3>
                <p class="directivo-cargo">{{ $directivo->cargo }}</p>

                @if($directivo->grado_cargo)
                  <p class="directivo-grado">
                    <i class="bi bi-mortarboard-fill me-1"></i>
                    {{ $directivo->grado_cargo }}
                  </p>
                @endif

                @if($directivo->biografia && !$isPendiente)
                  <button class="btn btn-sm btn-outline-primary mt-2 ver-bio-btn"
                          data-bs-toggle="modal"
                          data-bs-target="#bioModal{{ $directivo->directivo_id }}">
                    <i class="bi bi-info-circle me-1"></i>Ver biografía
                  </button>
                @endif
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
</section>


@foreach($directivos as $directivo)
  @php
    $isPendiente = stripos($directivo->nombre_completo, 'pendiente') !== false;
  @endphp
  @if($directivo->biografia && !$isPendiente)
  <div class="modal fade bio-modal-custom" id="bioModal{{ $directivo->directivo_id }}" tabindex="-1"
       aria-labelledby="bioModalLabel{{ $directivo->directivo_id }}" aria-hidden="true"
       data-bs-backdrop="static" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="bioModalLabel{{ $directivo->directivo_id }}">
            <i class="bi bi-person-badge me-2"></i>{{ $directivo->nombre_completo }}
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2"><strong><i class="bi bi-briefcase me-2 text-primary"></i>Cargo:</strong> {{ $directivo->cargo }}</p>
          @if($directivo->grado_cargo)
            <p class="mb-3"><strong><i class="bi bi-mortarboard-fill me-2 text-primary"></i>Grado:</strong> {{ $directivo->grado_cargo }}</p>
          @endif
          <hr>
          <div class="biografia-content">
            <h6 class="fw-bold mb-2"><i class="bi bi-file-text me-2 text-primary"></i>Biografía</h6>
            <p style="text-align: justify; line-height: 1.6;">{{ $directivo->biografia }}</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Cerrar
          </button>
        </div>
      </div>
    </div>
  </div>
  @endif
@endforeach
@endsection

@push('scripts')

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {

    AOS.init({
      duration: 600,
      easing: 'ease-in-out',
      once: true,
      offset: 50
    });


    const filterButtons = document.querySelectorAll('.filter-btn');
    const directivoItems = document.querySelectorAll('.directivo-item');

    filterButtons.forEach(button => {
      button.addEventListener('click', function() {

        filterButtons.forEach(btn => btn.classList.remove('active'));

        this.classList.add('active');

        const filter = this.getAttribute('data-filter');

        directivoItems.forEach(item => {
          if (filter === 'all') {
            item.style.display = 'block';
          } else {
            const itemGrado = item.getAttribute('data-grado');
            if (itemGrado === filter || itemGrado === 'general') {
              item.style.display = 'block';
            } else {
              item.style.display = 'none';
            }
          }
        });
      });
    });


    const bioModals = document.querySelectorAll('.bio-modal-custom');

    bioModals.forEach(modal => {

      modal.addEventListener('shown.bs.modal', function (e) {

        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
          backdrop.style.zIndex = '1055';
          backdrop.style.opacity = '0.2';  
          backdrop.style.backgroundColor = 'rgba(0, 0, 0, 0.2)';
        }
       
        this.style.zIndex = '1060';
      });

 
      modal.addEventListener('hidden.bs.modal', function (e) {
        setTimeout(() => {

          const backdrops = document.querySelectorAll('.modal-backdrop');
          backdrops.forEach(backdrop => backdrop.remove());


          document.body.classList.remove('modal-open');
          document.body.style.overflow = '';
          document.body.style.paddingRight = '';
        }, 100);
      });
    });
  });
</script>
@endpush
