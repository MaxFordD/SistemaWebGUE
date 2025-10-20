@extends('layouts.app')

@section('title', 'Comité Directivo - I.E. JFSC')
@section('body_class', 'page-comite-directivo')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/comite-directivo.css') }}" />
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
@endpush

@section('content')
<!-- Hero Section -->
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

<!-- Filtros por Grado (opcional - se puede implementar con JavaScript) -->
<section class="filtros py-3 bg-white border-bottom">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-center gap-2" data-aos="fade-up">
      <button class="btn btn-sm btn-outline-primary active filter-btn" data-filter="all">
        Todos
      </button>
      <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="1">
        1° Grado
      </button>
      <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="2">
        2° Grado
      </button>
      <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="3">
        3° Grado
      </button>
      <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="4">
        4° Grado
      </button>
      <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="5">
        5° Grado
      </button>
      <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="general">
        Gestión General
      </button>
    </div>
  </div>
</section>

<!-- Grid de Directivos -->
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

          <!-- Modal de Biografía -->
          @if($directivo->biografia && !$isPendiente)
          <div class="modal fade" id="bioModal{{ $directivo->directivo_id }}" tabindex="-1"
               aria-labelledby="bioModalLabel{{ $directivo->directivo_id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="bioModalLabel{{ $directivo->directivo_id }}">
                    {{ $directivo->nombre_completo }}
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                  <p class="mb-2"><strong>Cargo:</strong> {{ $directivo->cargo }}</p>
                  @if($directivo->grado_cargo)
                    <p class="mb-3"><strong>Grado:</strong> {{ $directivo->grado_cargo }}</p>
                  @endif
                  <hr>
                  <div class="biografia-content">
                    {{ $directivo->biografia }}
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
              </div>
            </div>
          </div>
          @endif
        @endforeach
      </div>
    @endif
  </div>
</section>
@endsection

@push('scripts')
<!-- AOS (Animate On Scroll) -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Inicializar AOS
    AOS.init({
      duration: 600,
      easing: 'ease-in-out',
      once: true,
      offset: 50
    });

    // Filtrado por grado
    const filterButtons = document.querySelectorAll('.filter-btn');
    const directivoItems = document.querySelectorAll('.directivo-item');

    filterButtons.forEach(button => {
      button.addEventListener('click', function() {
        // Remover clase active de todos los botones
        filterButtons.forEach(btn => btn.classList.remove('active'));
        // Agregar clase active al botón clickeado
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
  });
</script>
@endpush
