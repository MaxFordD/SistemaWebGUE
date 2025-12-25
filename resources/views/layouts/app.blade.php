<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'I.E. GUEJFSC')</title>

  <!-- Favicons -->
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

  <!-- Theming + SEO mínimo -->
  <meta name="theme-color" content="#7a1a0c">
  <meta name="description" content="Colegio José Faustino Sánchez Carrión - Trujillo. Noticias, talleres, galería e información institucional.">
  <meta name="msapplication-TileColor" content="#7a1a0c">
  <meta name="msapplication-config" content="{{ asset('browserconfig.xml') }}">

  <!-- Bootstrap 5.3.8 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Vite: Compilación de assets (CSS y JS) -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  @stack('styles')
</head>

<body class="@yield('body_class')">

  <a class="skip-link" href="#main-content">Saltar al contenido</a>
  <header class="header-sticky" role="banner">
    <nav class="navbar navbar-expand-lg navbar-dark bg-gue nav-elevable" aria-label="Navegación principal">
      <div class="container">
        <!-- Logo y marca -->
        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}" aria-label="Inicio">
          <img class="brand-logo" src="{{ asset('images/INSIGNIA G.U.E..png') }}"
            alt="Insignia del colegio José Faustino Sánchez Carrión" loading="lazy" decoding="async" />
          <span class="ms-2 d-none d-sm-inline text-center">
            JOSÉ FAUSTINO<br />SÁNCHEZ CARRIÓN
          </span>
        </a>

        <!-- Botón hamburguesa -->
        <button class="navbar-toggler" type="button"
          data-bs-toggle="collapse" data-bs-target="#navbarNav"
          aria-controls="navbarNav" aria-expanded="false" aria-label="Abrir menú">
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navegación colapsable -->
        <div class="collapse navbar-collapse" id="navbarNav">
          <!-- Contenedor único para todos los elementos de navegación a la derecha -->
          <div class="navbar-nav ms-auto align-items-lg-center">
            <!-- Links principales -->
            <div class="d-flex flex-column flex-lg-row align-items-lg-center">
              <a class="nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}"
                href="{{ route('home') }}"
                @if(request()->routeIs('home')) aria-current="page" @endif>
                Inicio
              </a>
              <a class="nav-link {{ request()->routeIs('noticias.*') ? 'is-active' : '' }}"
                href="{{ route('noticias.index') }}"
                @if(request()->routeIs('noticias.*')) aria-current="page" @endif>
                Noticias
              </a>
              <a class="nav-link {{ request()->routeIs('mesa.*') ? 'is-active' : '' }}"
                href="{{ route('mesa.create') }}"
                @if(request()->routeIs('mesa.*')) aria-current="page" @endif>
                Mesa de Partes
              </a>
              <a class="nav-link {{ request()->routeIs('nosotros') ? 'is-active' : '' }}"
                href="{{ route('nosotros') }}"
                @if(request()->routeIs('nosotros')) aria-current="page" @endif>
                Nosotros
              </a>
              <a class="nav-link {{ request()->routeIs('comite-directivo') ? 'is-active' : '' }}"
                href="{{ route('comite-directivo') }}"
                @if(request()->routeIs('comite-directivo')) aria-current="page" @endif>
                Comité Directivo
              </a>
            </div>

            <!-- Separador visual en desktop -->
            <div class="nav-separator d-none d-lg-block"></div>

            <!-- Botones de autenticación -->
            <div class="d-flex align-items-center auth-section">
              @guest
              <a class="btn btn-outline-light btn-sm px-4 fw-semibold" href="{{ route('login') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-circle me-2" viewBox="0 0 16 16" style="vertical-align: -2px;">
                  <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                  <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z" />
                </svg>
                Ingresar
              </a>
              @endguest

              @auth
              @php
              // === Cargar roles del usuario autenticado usando tus SP ===
              // Soporta nombres de rol: Director (admin), Admin, Administrador, Editor
              $rolesUser = collect();
              $isAdminLike = false; // Director/Admin/Administrador
              $puedePublicar = false; // Director/Admin/Administrador/Editor

              try {
              $u = auth()->user();
              // 1) Resolver ID real en tu tabla Usuario (puede ser usuario_id o id)
              $uid = $u->usuario_id ?? $u->id ?? null;

              // 2) Si no hay uid, intentar mapear por nombre_usuario
              if (!$uid && !empty($u->nombre_usuario)) {
              $row = DB::select('SELECT usuario_id FROM Usuario WHERE nombre_usuario = ? LIMIT 1', [$u->nombre_usuario]);
              if (!empty($row)) $uid = (int) $row[0]->usuario_id;
              }
              // 3) O por email contra Persona.correo
              if (!$uid && !empty($u->email)) {
              $row = DB::select('
              SELECT u.usuario_id
              FROM Usuario u
              INNER JOIN Persona p ON u.persona_id = p.persona_id
              WHERE p.correo = ?
              LIMIT 1
              ', [$u->email]);
              if (!empty($row)) $uid = (int) $row[0]->usuario_id;
              }

              // 4) Con uid, traer roles
              if ($uid) {
              $rolesUser = collect(DB::select('CALL sp_UsuarioRol_ListarPorUsuario(?)', [$uid]))
              ->pluck('nombre')
              ->filter()
              ->map(fn($n) => mb_strtolower(trim($n)));
              }

              // 5) Flags de permisos
              $isAdminLike = $rolesUser->contains(fn($r) => in_array($r, ['director','admin','administrador']));
              $puedeMesaPartes = $isAdminLike || $rolesUser->contains('mesapartes');
              $puedePublicar = $isAdminLike || $rolesUser->contains(fn($r) => in_array($r, ['editor','secretaria']));
              } catch (\Throwable $e) {
              $rolesUser = collect();
              $isAdminLike = false;
              $puedeMesaPartes = false;
              $puedePublicar = false;
              }
              @endphp

              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center user-menu-toggle"
                  href="#"
                  role="button"
                  data-bs-toggle="dropdown"
                  data-bs-auto-close="true"
                  aria-expanded="false">
                  <div class="user-avatar me-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                      <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z" />
                    </svg>
                  </div>
                  <span class="user-name">{{ auth()->user()->nombre_usuario ?? 'Usuario' }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg">
                  <!-- Panel -->
                  <li>
                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                      <i class="bi bi-speedometer2 me-2"></i>Panel de Control
                    </a>
                  </li>

                  <!-- Gestión de Usuarios (solo Director/Admin/Administrador) -->
                  @if($isAdminLike)
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li class="dropdown-header">Gestión de Usuarios</li>
                  <li><a class="dropdown-item" href="{{ route('admin.personas.index') }}"><i class="bi bi-person-badge me-2"></i>Gestionar Personas</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.usuarios.index') }}"><i class="bi bi-people me-2"></i>Gestionar Usuarios</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.usuario-rol.index') }}"><i class="bi bi-shield-check me-2"></i>Asignar Roles</a></li>
                  @endif

                  <!-- Mesa de Partes (Director/Admin/Administrador/MesaPartes) -->
                  @if($puedeMesaPartes)
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li class="dropdown-header">Mesa de Partes</li>
                  <li>
                    <a class="dropdown-item" href="{{ route('admin.mesa.index') }}">
                      <i class="bi bi-inbox me-2"></i>Ver Documentos Recibidos
                    </a>
                  </li>
                  @endif

                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li class="dropdown-header">Contenidos</li>


                  <!-- Publicar Noticia (Director/Admin/Administrador/Editor/Secretaria) -->
                  @if($puedePublicar)
                  <li>
                    <a class="dropdown-item" href="{{ route('noticias.create') }}">
                      <i class="bi bi-plus-circle me-2"></i>Publicar Noticia
                    </a>
                  </li>
                  @endif

                  <!-- Gestionar Comité Directivo (solo Director/Admin/Administrador) -->
                  @if($isAdminLike)
                  <li>
                    <a class="dropdown-item" href="{{ route('admin.comite-directivo.index') }}">
                      <i class="bi bi-people-fill me-2"></i>Gestionar Comité Directivo
                    </a>
                  </li>
                  @endif

                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                      @csrf
                      <button class="dropdown-item text-danger d-flex align-items-center" type="submit">
                        <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                      </button>
                    </form>
                  </li>
                </ul>
              </div>
              @endauth
            </div>
          </div>
        </div>
      </div>
    </nav>
  </header>

  <!-- Resto del código permanece igual -->
  <section class="masthead-waves" aria-hidden="true">
    <div class="waves-wrap">
      <svg viewBox="0 0 1200 120" preserveAspectRatio="none" focusable="false" aria-hidden="true">
        <path opacity=".25" class="shape-fill" d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z"></path>
        <path opacity=".5" class="shape-fill" d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z"></path>
        <path class="shape-fill" d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z"></path>
      </svg>
    </div>
  </section>

  <!-- Contenedor principal que crece con flexbox -->
  <div class="main-wrapper">
    {{-- Flash messages globales mejorados --}}
    <div class="container mt-3">
      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show modern-alert shadow-sm" role="alert">
        <div class="d-flex align-items-center">
          <i class="bi bi-check-circle-fill fs-4 me-3"></i>
          <div class="flex-grow-1">{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
      @endif
      @if(session('ok'))
      <div class="alert alert-success alert-dismissible fade show modern-alert shadow-sm" role="alert">
        <div class="d-flex align-items-center">
          <i class="bi bi-check-circle-fill fs-4 me-3"></i>
          <div class="flex-grow-1">{{ session('ok') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
      @endif
      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show modern-alert shadow-sm" role="alert">
        <div class="d-flex align-items-center">
          <i class="bi bi-exclamation-circle-fill fs-4 me-3"></i>
          <div class="flex-grow-1">{{ session('error') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
      @endif
      @if(session('warning'))
      <div class="alert alert-warning alert-dismissible fade show modern-alert shadow-sm" role="alert">
        <div class="d-flex align-items-center">
          <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
          <div class="flex-grow-1">{{ session('warning') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
      @endif
      @if(session('info'))
      <div class="alert alert-info alert-dismissible fade show modern-alert shadow-sm" role="alert">
        <div class="d-flex align-items-center">
          <i class="bi bi-info-circle-fill fs-4 me-3"></i>
          <div class="flex-grow-1">{{ session('info') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
      @endif
    </div>

    <main id="main-content" class="container mt-1 @yield('main_class')" role="main" tabindex="-1">
      @yield('content')
    </main>
  </div>

  <footer class="site-footer" role="contentinfo">
    <div class="container py-5">
      <div class="row g-4">
        <!-- Columna 1: Información institucional + Enlaces -->
        <div class="col-lg-3 col-md-6 col-12">
          <h5 class="fw-bold mb-3 text-white">I.E. José Faustino Sánchez Carrión</h5>
          <h6 class="fw-bold mb-3 text-white small">Enlaces Rápidos</h6>
          <ul class="list-unstyled mb-0">
            <li class="mb-2"><a href="{{ route('home') }}" class="footer-link"><i class="bi bi-chevron-right me-1"></i>Inicio</a></li>
            <li class="mb-2"><a href="{{ route('nosotros') }}" class="footer-link"><i class="bi bi-chevron-right me-1"></i>Nosotros</a></li>
            <li class="mb-2"><a href="{{ route('noticias.index') }}" class="footer-link"><i class="bi bi-chevron-right me-1"></i>Noticias</a></li>
            <li class="mb-2"><a href="{{ route('comite-directivo') }}" class="footer-link"><i class="bi bi-chevron-right me-1"></i>Comité Directivo</a></li>
            <li class="mb-2"><a href="{{ route('mesa.create') }}" class="footer-link"><i class="bi bi-chevron-right me-1"></i>Mesa de Partes</a></li>
          </ul>
        </div>

        <!-- Columna 2: Información de contacto -->
        <div class="col-lg-3 col-md-6 col-12">
          <h6 class="fw-bold mb-3 text-white">Contacto</h6>
          <ul class="list-unstyled footer-text small mb-0">
            <li class="mb-2 d-flex align-items-start">
              <i class="bi bi-geo-alt-fill text-warning me-2 mt-1 flex-shrink-0"></i>
              <span>Av. Moche 990<br>Trujillo, La Libertad</span>
            </li>
            <li class="mb-2 d-flex align-items-center">
              <i class="bi bi-telephone-fill text-warning me-2 flex-shrink-0"></i>
              <span>927 803 520</span>
            </li>
            <li class="mb-2 d-flex align-items-center">
              <i class="bi bi-envelope-fill text-warning me-2 flex-shrink-0"></i>
              <a href="mailto:contacto@iejfsc.edu.pe" class="footer-link">contacto@iejfsc.edu.pe</a>
            </li>
            <li class="mb-2 d-flex align-items-center">
              <i class="bi bi-clock-fill text-warning me-2 flex-shrink-0"></i>
              <span>Lun - Vie: 8:00 AM - 3:00 PM</span>
            </li>
          </ul>

          <!-- Redes Sociales -->
          <div class="mt-3">
            <h6 class="fw-bold mb-2 text-white small">Síguenos en Redes</h6>
            <a href="https://www.facebook.com/share/17mn3mct7J/" target="_blank" rel="noopener noreferrer" class="btn btn-facebook d-inline-flex align-items-center gap-2" aria-label="Visítanos en Facebook">
              <i class="bi bi-facebook fs-5"></i>
              <span class="fw-semibold">Facebook</span>
            </a>
          </div>
        </div>

        <!-- Columna 3: Mapa -->
        <div class="col-lg-6 col-md-12 col-12">
          <h6 class="fw-bold mb-3 text-white">Nuestra Ubicación</h6>
          <div class="footer-map">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3949.989019787752!2d-79.0267859!3d-8.122970199999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x91ad3d7c75297dad%3A0x7dfdf768dab093b4!2sInstituci%C3%B3n%20Educativa%20Jos%C3%A9%20Faustino%20S%C3%A1nchez%20Carri%C3%B3n!5e0!3m2!1ses-419!2spe!4v1732636610000"
              width="100%"
              height="200"
              style="border:0; border-radius: 8px;"
              allowfullscreen=""
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              title="Mapa de ubicación de I.E. José Faustino Sánchez Carrión">
            </iframe>
          </div>
        </div>
      </div>

      <hr class="my-4 border-light opacity-25">

      <div class="row">
        <div class="col-12 text-center">
          <p class="footer-text small mb-0">
            &copy; {{ date('Y') }} I.E. Emblemática José Faustino Sánchez Carrión - Todos los derechos reservados
          </p>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scroll to Top Button -->
  <button id="scrollToTop" class="scroll-to-top" aria-label="Volver arriba" title="Volver arriba">
    <i class="bi bi-arrow-up"></i>
  </button>

  <!-- JS: Bootstrap 5 bundle (Popper incluido). Sin jQuery requerido. -->
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script defer src="{{ asset('js/navbar.js') }}"></script>
  <script defer src="{{ asset('js/scroll-to-top.js') }}"></script>
  @stack('scripts')
</body>

</html>