<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'I.E. GUEJFSC')</title>

  <!-- Theming + SEO mínimo -->
  <meta name="theme-color" content="#7a1a0c">
  <meta name="description" content="Colegio José Faustino Sánchez Carrión - Trujillo. Noticias, talleres, galería e información institucional.">

  <!-- Bootstrap 5.3.8 + estilos del proyecto -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/layout.css') }}" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

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
              $row = DB::select('SELECT TOP 1 usuario_id FROM Usuario WHERE nombre_usuario = ?', [$u->nombre_usuario]);
              if (!empty($row)) $uid = (int) $row[0]->usuario_id;
              }
              // 3) O por email contra Persona.correo
              if (!$uid && !empty($u->email)) {
              $row = DB::select('
              SELECT TOP 1 u.usuario_id
              FROM Usuario u
              INNER JOIN Persona p ON u.persona_id = p.persona_id
              WHERE p.correo = ?
              ', [$u->email]);
              if (!empty($row)) $uid = (int) $row[0]->usuario_id;
              }

              // 4) Con uid, traer roles
              if ($uid) {
              $rolesUser = collect(DB::select('EXEC sp_UsuarioRol_ListarPorUsuario ?', [$uid]))
              ->pluck('nombre')
              ->filter()
              ->map(fn($n) => mb_strtolower(trim($n)));
              }

              // 5) Flags: Director es admin-like
              $isAdminLike = $rolesUser->contains(fn($r) => in_array($r, ['director','admin','administrador']));
              $puedePublicar = $isAdminLike || $rolesUser->contains('editor');
              } catch (\Throwable $e) {
              $rolesUser = collect();
              $isAdminLike = false;
              $puedePublicar = false;
              }
              @endphp

              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center user-menu-toggle"
                  href="#"
                  role="button"
                  data-bs-toggle="dropdown"
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
                      Panel de Control
                    </a>
                  </li>

                  <!-- Gestión de Usuarios (solo Director/Admin/Administrador) -->
                  @if($isAdminLike)
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li class="dropdown-header">Gestión de Usuarios</li>
                  <li><a class="dropdown-item" href="{{ route('admin.personas.index') }}">Gestionar Personas</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.usuarios.index') }}">Gestionar Usuarios</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.usuario-rol.index') }}">Asignar Roles</a></li>
                  @endif

                  <!-- Roles y permisos (solo Director/Admin/Administrador) -->
                  @if($isAdminLike)
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li class="dropdown-header">Roles y permisos</li>
                  <li><a class="dropdown-item" href="{{ route('admin.roles.index') }}">Gestionar roles</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.roles.assign') }}">Asignar rol a usuario (antiguo)</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.roles.users') }}">Usuarios con roles</a></li>
                  @endif

                  @if($isAdminLike)
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li class="dropdown-header">Mesa de Partes</li>
                  <li>
                    <a class="dropdown-item" href="{{ route('admin.mesa.index') }}">
                      Ver Documentos Recibidos
                    </a>
                  </li>
                  @endif

                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li class="dropdown-header">Contenidos</li>


                  <!-- Publicar Noticia (Director/Admin/Administrador o Editor) -->
                  @if($puedePublicar)
                  <li>
                    <a class="dropdown-item" href="{{ route('noticias.create') }}">
                      Publicar Noticia
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
                        Cerrar Sesión
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

  {{-- Flash messages globales --}}
  <div class="container mt-3">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    @endif
    @if(session('ok'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('ok') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    @endif
  </div>

  <main id="main-content" class="container mt-1 @yield('main_class')" role="main" tabindex="-1">
    @yield('content')
  </main>

  <footer class="site-footer" role="contentinfo">
    <div class="container py-3 text-center">
      <p class="mb-0">&copy; {{ date('Y') }} JOSE FAUSTINO SANCHEZ CARRION</p>
    </div>
  </footer>

  <!-- JS: Bootstrap 5 bundle (Popper incluido). Sin jQuery requerido. -->
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script defer src="{{ asset('js/navbar.js') }}"></script>
  @stack('scripts')
</body>

</html>