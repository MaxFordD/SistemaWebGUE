<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
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

  <!-- Bootstrap y Bootstrap Icons se auto-hospedan vía Vite (resources/css/app.css) -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    /* Evita que los nav-links rompan línea en desktop */
    .navbar-nav .nav-link { white-space: nowrap; font-size: .875rem; }
  </style>
  @stack('styles')
</head>

<body class="@yield('body_class')">

  <a class="skip-link" href="#main-content">Saltar al contenido</a>
  <header class="header-sticky" role="banner">
    <nav class="navbar navbar-expand-xl navbar-dark bg-gue nav-elevable" aria-label="Navegación principal">
      <div class="container">
        <!-- Logo y marca -->
        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}" aria-label="Inicio">
          <img class="brand-logo" src="{{ asset('images/INSIGNIA G.U.E..png') }}"
            alt="Insignia del colegio José Faustino Sánchez Carrión" width="320" height="313"
            loading="eager" decoding="async" />
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
              <a class="nav-link {{ request()->routeIs('historia-legado') ? 'is-active' : '' }}"
                href="{{ route('historia-legado') }}"
                @if(request()->routeIs('historia-legado')) aria-current="page" @endif>
                Historia y Legado
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
              $rolesUser     = collect();
              $userPermisos  = collect();
              $esAdmin       = false;
              $isAdminLike   = false;
              $puedeVerPersonas    = false;
              $puedeVerUsuarios    = false;
              $puedeAsignarRoles   = false;
              $puedeGestionarRoles = false;
              $puedePermisos       = false;
              $puedePublicar       = false;
              $puedeComite         = false;
              $puedeHistoria       = false;
              $puedeImagenes       = false;
              $puedeNosotros       = false;
              $puedeMesaPartes     = false;
              $puedeBitacora       = false;
              $puedeAlumnos        = false;
              $puedeGrados         = false;
              $puedeSecciones      = false;
              $puedeRegistrar      = false;
              $puedeReportes       = false;
              $puedeAsistencia     = false;
              $mesaPartesPendientes = 0;

              try {
              $u   = auth()->user();
              $uid = $u->usuario_id ?? $u->id ?? null;

              // Cargar roles desde sesión o SP
              $sessionRoles = session('user_roles');
              if ($sessionRoles !== null) {
                  $rolesUser = collect($sessionRoles);
              } elseif ($uid) {
                  $fetched = collect(DB::select('CALL sp_UsuarioRol_ListarPorUsuario(?)', [(int)$uid]))
                      ->pluck('nombre')->filter()->map(fn($n) => mb_strtolower(trim($n)))->toArray();
                  session(['user_roles' => $fetched]);
                  $rolesUser = collect($fetched);
              }

              $esAdmin     = $rolesUser->contains(fn($r) => in_array($r, ['administrador','admin','director']));
              $isAdminLike = $esAdmin;

              // Cargar permisos desde sesión o DB
              try {
                  $sessionPermisos = session('user_permisos');
                  if ($sessionPermisos !== null) {
                      $userPermisos = collect($sessionPermisos);
                  } elseif ($uid) {
                      $slugs = $u->roles()
                          ->with(['permisos' => fn($q) => $q->where('estado', 'A')])
                          ->get()
                          ->flatMap(fn($r) => $r->permisos->pluck('slug'))
                          ->unique()->toArray();
                      session(['user_permisos' => $slugs]);
                      $userPermisos = collect($slugs);
                  }
              } catch (\Throwable $e) { /* tabla Permiso aún no existe */ }

              $hasP = fn($slug) => $esAdmin || $userPermisos->contains($slug);

              $puedeVerPersonas    = $hasP('personas.admin');
              $puedeVerUsuarios    = $hasP('usuarios.admin');
              $puedeAsignarRoles   = $hasP('roles.asignar');
              $puedeGestionarRoles = $hasP('roles.admin');
              $puedePermisos       = $hasP('permisos.admin');
              $puedePublicar       = $hasP('noticias.admin');
              $puedeComite         = $hasP('comite.admin');
              $puedeHistoria       = $hasP('historia.admin');
              $puedeImagenes       = $hasP('imagenes.admin');
              $puedeNosotros       = $hasP('nosotros.admin');
              $puedeMesaPartes     = $hasP('mesa.admin');
              $puedeAlumnos        = $hasP('alumnos.admin');
              $puedeBitacora       = $hasP('bitacora.ver') || $hasP('bitacora.ver_mesa_partes');
              $puedeGrados         = $hasP('grados.admin');
              $puedeSecciones      = $hasP('secciones.admin');
              $puedeRegistrar      = $hasP('asistencia.registrar');
              $puedeReportes       = $hasP('asistencia.reportes');
              $puedeConfigAsistencia = $hasP('asistencia.configurar');
              $puedeAsistencia     = $puedeAlumnos || $puedeGrados || $puedeSecciones || $puedeRegistrar || $puedeReportes || $puedeConfigAsistencia;

              if ($puedeMesaPartes) {
                  try {
                      $mesaPartesPendientes = collect(DB::select('CALL sp_MesaPartes_Listar()'))
                          ->where('estado', 'Pendiente')->count();
                  } catch (\Throwable $e) {}
              }
              } catch (\Throwable $e) { /* defaults ya seteados arriba */ }
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
                <ul class="dropdown-menu dropdown-menu-end shadow-lg mega-user-menu">

                  {{-- ===== COLUMNAS HORIZONTALES ===== --}}
                  <li>
                    <div class="mega-cols">

                      {{-- Columna 1: Mi cuenta + Usuarios --}}
                      <div class="mega-col">
                        <span class="dropdown-header">Mi cuenta</span>
                        <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                          <i class="bi bi-speedometer2 me-2"></i>Panel de Control
                        </a>
                        <a class="dropdown-item" href="{{ route('perfil.contrasena') }}">
                          <i class="bi bi-key me-2"></i>Cambiar contraseña
                        </a>
                        @if($puedeBitacora)
                        <a class="dropdown-item" href="{{ route('admin.bitacora.index') }}">
                          <i class="bi bi-journal-text me-2"></i>Bitácora
                        </a>
                        @endif
                        @if($puedeVerPersonas || $puedeVerUsuarios || $puedeAsignarRoles || $puedeGestionarRoles || $puedePermisos)
                        <span class="dropdown-header mt-2">Usuarios</span>
                        @if($puedeVerPersonas)
                        <a class="dropdown-item" href="{{ route('admin.personas.index') }}">
                          <i class="bi bi-person-badge me-2"></i>Personas
                        </a>
                        @endif
                        @if($puedeVerUsuarios)
                        <a class="dropdown-item" href="{{ route('admin.usuarios.index') }}">
                          <i class="bi bi-people me-2"></i>Usuarios
                        </a>
                        @endif
                        @if($puedeGestionarRoles)
                        <a class="dropdown-item" href="{{ route('admin.roles.index') }}">
                          <i class="bi bi-person-gear me-2"></i>Roles
                        </a>
                        @endif
                        @if($puedeAsignarRoles)
                        <a class="dropdown-item" href="{{ route('admin.usuario-rol.index') }}">
                          <i class="bi bi-shield-check me-2"></i>Asignar Roles
                        </a>
                        @endif
                        @if($puedePermisos)
                        <a class="dropdown-item" href="{{ route('admin.roles-permisos.index') }}">
                          <i class="bi bi-lock me-2"></i>Permisos por Rol
                        </a>
                        @endif
                        @endif
                      </div>

                      {{-- Columna 2: Contenidos + Mesa de Partes --}}
                      @if($puedePublicar || $puedeComite || $puedeHistoria || $puedeImagenes || $puedeNosotros || $puedeMesaPartes)
                      <div class="mega-col">
                        @if($puedePublicar || $puedeComite || $puedeHistoria || $puedeImagenes || $puedeNosotros)
                        <span class="dropdown-header">Contenidos</span>
                        @if($puedePublicar)
                        <a class="dropdown-item" href="{{ route('noticias.create') }}">
                          <i class="bi bi-plus-circle me-2"></i>Publicar Noticia
                        </a>
                        @endif
                        @if($puedeComite)
                        <a class="dropdown-item" href="{{ route('admin.comite-directivo.index') }}">
                          <i class="bi bi-people-fill me-2"></i>Comité Directivo
                        </a>
                        @endif
                        @if($puedeHistoria)
                        <a class="dropdown-item" href="{{ route('admin.historia-legado.index') }}">
                          <i class="bi bi-hourglass-split me-2"></i>Historia y Legado
                        </a>
                        @endif
                        @if($puedeImagenes)
                        <a class="dropdown-item" href="{{ route('admin.imagenes-inicio.index') }}">
                          <i class="bi bi-images me-2"></i>Imágenes del Inicio
                        </a>
                        @endif
                        @if($puedeNosotros)
                        <a class="dropdown-item" href="{{ route('admin.nosotros.edit') }}">
                          <i class="bi bi-info-circle me-2"></i>Nosotros
                        </a>
                        @endif
                        @endif
                        @if($puedeMesaPartes)
                        <span class="dropdown-header mt-2">Mesa de Partes</span>
                        <a class="dropdown-item" href="{{ route('admin.mesa.index') }}">
                          <i class="bi bi-inbox me-2"></i>Ver Documentos
                          @if($mesaPartesPendientes > 0)
                            <span class="badge bg-danger ms-auto">{{ $mesaPartesPendientes }}</span>
                          @endif
                        </a>
                        @endif
                      </div>
                      @endif

                      {{-- Columna 3: Asistencia --}}
                      @if($puedeAsistencia)
                      <div class="mega-col">
                        <span class="dropdown-header">Asistencia</span>
                        @if($puedeGrados)
                        <a class="dropdown-item" href="{{ route('admin.grados.index') }}">
                          <i class="bi bi-journal-text me-2"></i>Grados
                        </a>
                        @endif
                        @if($puedeSecciones)
                        <a class="dropdown-item" href="{{ route('admin.secciones.index') }}">
                          <i class="bi bi-diagram-3 me-2"></i>Secciones
                        </a>
                        @endif
                        @if($puedeAlumnos)
                        <a class="dropdown-item" href="{{ route('admin.alumnos.index') }}">
                          <i class="bi bi-people me-2"></i>Alumnos
                        </a>
                        @endif
                        @if($puedeRegistrar)
                        <a class="dropdown-item" href="{{ route('admin.asistencia.index') }}">
                          <i class="bi bi-calendar-check me-2"></i>Registrar
                        </a>
                        @endif
                        @if($puedeReportes)
                        <a class="dropdown-item" href="{{ route('admin.asistencia.historial-seccion') }}">
                          <i class="bi bi-bar-chart-line me-2"></i>Historial
                        </a>
                        @endif
                        @if($puedeConfigAsistencia)
                        <a class="dropdown-item" href="{{ route('admin.asistencia.configuracion.index') }}">
                          <i class="bi bi-gear me-2"></i>Configuración
                        </a>
                        <a class="dropdown-item" href="{{ route('admin.asistencia.dias-no-habiles.index') }}">
                          <i class="bi bi-calendar-x me-2"></i>Días no hábiles
                        </a>
                        @endif
                      </div>
                      @endif

                    </div>
                  </li>

                  {{-- ===== PIE: Cerrar Sesión ===== --}}
                  <li>
                    <div class="mega-footer">
                      <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button class="dropdown-item text-danger d-flex align-items-center" type="submit">
                          <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                        </button>
                      </form>
                    </div>
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
      @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show modern-alert shadow-sm" role="alert">
        <div class="d-flex align-items-start">
          <i class="bi bi-exclamation-circle-fill fs-4 me-3"></i>
          <div class="flex-grow-1">
            <strong>Revisa lo siguiente:</strong>
            <ul class="mb-0 mt-1">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
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
    <div class="container py-2 py-lg-3">
      <div class="row g-2">
        <!-- Columna 1: Enlaces -->
        <div class="col-lg-3 col-6">
          <h6 class="fw-bold mb-2 text-white small">Enlaces Rápidos</h6>
          <ul class="list-unstyled mb-0">
            <li class="mb-1"><a href="{{ route('home') }}" class="footer-link"><i class="bi bi-chevron-right me-1"></i>Inicio</a></li>
            <li class="mb-1"><a href="{{ route('nosotros') }}" class="footer-link"><i class="bi bi-chevron-right me-1"></i>Nosotros</a></li>
            <li class="mb-1"><a href="{{ route('noticias.index') }}" class="footer-link"><i class="bi bi-chevron-right me-1"></i>Noticias</a></li>
            <li class="mb-1"><a href="{{ route('comite-directivo') }}" class="footer-link"><i class="bi bi-chevron-right me-1"></i>Comité Directivo</a></li>
            <li class="mb-1"><a href="{{ route('historia-legado') }}" class="footer-link"><i class="bi bi-chevron-right me-1"></i>Historia y Legado</a></li>
            <li class="mb-1"><a href="{{ route('mesa.create') }}" class="footer-link"><i class="bi bi-chevron-right me-1"></i>Mesa de Partes</a></li>
          </ul>
        </div>

        <!-- Columna 2: Información de contacto -->
        <div class="col-lg-3 col-6 text-center">
          <h6 class="fw-bold mb-2 text-white">Contacto</h6>
          <ul class="list-unstyled footer-text small mb-0">
            <li class="mb-1 d-flex align-items-start justify-content-center">
              <i class="bi bi-geo-alt-fill text-warning me-2 mt-1 flex-shrink-0"></i>
              <span>Av. Moche 990<br>Trujillo, La Libertad</span>
            </li>
            <li class="mb-1 d-flex align-items-center justify-content-center">
              <i class="bi bi-telephone-fill text-warning me-2 flex-shrink-0"></i>
              <span>927 803 520</span>
            </li>
            <li class="mb-1 d-flex align-items-center justify-content-center">
              <i class="bi bi-envelope-fill text-warning me-2 flex-shrink-0"></i>
              <a href="mailto:contacto@iejfsc.edu.pe" class="footer-link">contacto@iejfsc.edu.pe</a>
            </li>
            <li class="mb-1 d-flex align-items-center justify-content-center">
              <i class="bi bi-clock-fill text-warning me-2 flex-shrink-0"></i>
              <span>Lun - Vie: 8:00 AM - 3:00 PM</span>
            </li>
          </ul>

          <!-- Redes Sociales -->
          <div class="mt-2">
            <h6 class="fw-bold mb-2 text-white small">Síguenos en Redes</h6>
            <a href="https://www.facebook.com/share/17mn3mct7J/" target="_blank" rel="noopener noreferrer" class="btn btn-facebook d-inline-flex align-items-center gap-2" aria-label="Visítanos en Facebook">
              <i class="bi bi-facebook fs-5"></i>
              <span class="fw-semibold">Facebook</span>
            </a>
          </div>
        </div>

        <!-- Columna 3: Mapa -->
        <div class="col-lg-6 col-12">
          <h6 class="fw-bold mb-2 text-white">Nuestra Ubicación</h6>
          <div class="footer-map">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3949.989019787752!2d-79.0267859!3d-8.122970199999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x91ad3d7c75297dad%3A0x7dfdf768dab093b4!2sInstituci%C3%B3n%20Educativa%20Jos%C3%A9%20Faustino%20S%C3%A1nchez%20Carri%C3%B3n!5e0!3m2!1ses-419!2spe!4v1732636610000"
              width="100%"
              height="160"
              style="border:0; border-radius: 8px;"
              allowfullscreen=""
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              title="Mapa de ubicación de I.E. José Faustino Sánchez Carrión">
            </iframe>
          </div>
        </div>
      </div>

      <hr class="my-1 border-light opacity-25">

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


  @stack('modals')

  <!-- Bootstrap JS ya se carga vía Vite (resources/js/app.js). Sin jQuery requerido. -->
  <script defer src="{{ asset('js/navbar.js') }}"></script>
  <script defer src="{{ asset('js/scroll-to-top.js') }}"></script>
  @stack('scripts')
</body>

</html>