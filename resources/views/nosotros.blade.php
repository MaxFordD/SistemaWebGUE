@extends('layouts.app')

@section('title', 'Nosotros - I.E. JFSC')
@section('body_class', 'page-nosotros')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/nosotros.css') }}" />
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
@endpush

@section('content')
<!-- Hero Section -->
<section class="nosotros-hero py-5">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-6" data-aos="fade-right">
        <h1 class="display-4 fw-bold mb-4">Nuestra Historia</h1>
        <p class="lead text-muted">
          La Institución Educativa Emblemática José Faustino Sánchez Carrión es una institución con más de
          <strong>73 años de trayectoria</strong> formando ciudadanos íntegros en la ciudad de Trujillo.
        </p>
        <p class="text-muted">
          Fundada en 1831, nuestra institución ha sido pionera en la educación de calidad en La Libertad,
          manteniendo un compromiso inquebrantable con la excelencia académica y la formación en valores.
        </p>
      </div>
      <div class="col-lg-6" data-aos="fade-left">
        <div class="nosotros-hero-img rounded-3 shadow-lg overflow-hidden">
          <img src="{{ asset('images/gue.jpg') }}"
               alt="Fachada histórica del colegio José Faustino Sánchez Carrión"
               class="img-fluid w-100">
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Misión y Visión -->
<section class="mision-vision py-5 bg-light">
  <div class="container">
    <div class="row g-4">
      <!-- Misión -->
      <div class="col-md-6" data-aos="fade-up">
        <div class="card h-100 border-0 shadow-sm hover-lift">
          <div class="card-body p-4">
            <div class="icon-wrapper mb-3">
              <i class="bi bi-bullseye text-primary display-4"></i>
            </div>
            <h2 class="h3 fw-bold mb-3">Nuestra Misión</h2>
            <p class="text-muted mb-0">
              Formar estudiantes con excelencia académica, valores éticos y conciencia ciudadana,
              capaces de enfrentar los retos del mundo contemporáneo con pensamiento crítico,
              creatividad e innovación, contribuyendo al desarrollo sostenible de nuestra sociedad.
            </p>
          </div>
        </div>
      </div>

      <!-- Visión -->
      <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
        <div class="card h-100 border-0 shadow-sm hover-lift">
          <div class="card-body p-4">
            <div class="icon-wrapper mb-3">
              <i class="bi bi-eye text-primary display-4"></i>
            </div>
            <h2 class="h3 fw-bold mb-3">Nuestra Visión</h2>
            <p class="text-muted mb-0">
              Ser reconocidos como la institución educativa líder en la región, referente de
              calidad educativa e innovación pedagógica, formando ciudadanos competentes,
              comprometidos con su comunidad y preparados para construir un futuro mejor.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Pilares Institucionales -->
<section class="pilares py-5">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <h2 class="h1 fw-bold mb-3">Nuestros Pilares</h2>
      <p class="lead text-muted">Los valores que nos definen y guían nuestro quehacer educativo</p>
    </div>

    <div class="row g-4">
      @php
        $pilares = [
          [
            'icon' => 'heart-fill',
            'titulo' => 'Valores',
            'desc' => 'Respeto, responsabilidad, honestidad y solidaridad son la base de nuestra formación integral.',
            'color' => 'danger'
          ],
          [
            'icon' => 'book-half',
            'titulo' => 'Excelencia Académica',
            'desc' => 'Comprometidos con los más altos estándares educativos y la mejora continua.',
            'color' => 'primary'
          ],
          [
            'icon' => 'shield-check',
            'titulo' => 'Tradición',
            'desc' => 'Más de 190 años de historia formando generaciones de líderes trujillanos.',
            'color' => 'success'
          ],
          [
            'icon' => 'lightbulb-fill',
            'titulo' => 'Innovación',
            'desc' => 'Incorporamos tecnología y metodologías modernas para una educación del siglo XXI.',
            'color' => 'warning'
          ],
          [
            'icon' => 'people-fill',
            'titulo' => 'Inclusión',
            'desc' => 'Valoramos la diversidad y garantizamos oportunidades educativas para todos.',
            'color' => 'info'
          ],
          [
            'icon' => 'star-fill',
            'titulo' => 'Liderazgo',
            'desc' => 'Formamos líderes con pensamiento crítico y compromiso social.',
            'color' => 'dark'
          ],
        ];
      @endphp

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

<!-- Normas de Convivencia -->
<section class="normas py-5 bg-light">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <h2 class="h1 fw-bold mb-3">Normas de Convivencia</h2>
      <p class="lead text-muted">Compromisos que asumimos como comunidad educativa</p>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="normas-list" data-aos="fade-up" data-aos-delay="100">
          @php
            $normas = [
              'Respetar a todos los miembros de la comunidad educativa',
              'Cumplir con puntualidad y responsabilidad nuestros deberes',
              'Cuidar las instalaciones y materiales educativos',
              'Mantener un ambiente de convivencia armónica y pacífica',
              'Practicar la honestidad en todas nuestras acciones',
              'Valorar y respetar la diversidad cultural',
              'Resolver conflictos mediante el diálogo y la mediación',
              'Contribuir al cuidado del medio ambiente',
            ];
          @endphp

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
<!-- AOS (Animate On Scroll) -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    AOS.init({
      duration: 800,
      easing: 'ease-in-out',
      once: true,
      offset: 100
    });
  });
</script>
@endpush
