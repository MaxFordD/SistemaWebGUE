@extends('layouts.app')

@section('title', 'Noticias')
@section('body_class', 'waves-compact')

@push('scripts')
<script src="{{ asset('js/confirm-delete.js') }}"></script>
@endpush

@section('content')
<div class="py-3">
   
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h2 fw-bold mb-0">
            <i class="bi bi-newspaper me-2 text-primary"></i>Noticias
        </h1>
        @role('Editor','Administrador','Director')
            <a class="btn btn-sm btn-primary" href="{{ route('noticias.create') }}">
                <i class="bi bi-plus-circle me-1"></i>Publicar
            </a>
        @endrole
    </div>

    @if($noticias->isEmpty())
        <div class="alert alert-secondary d-flex align-items-center">
            <i class="bi bi-info-circle fs-4 me-3"></i>
            <div>No hay noticias publicadas aún.</div>
        </div>
    @else
        <div class="row g-3 g-md-4">
            @foreach($noticias as $n)
                @php
                    $fechaPublicacion = is_string($n->fecha_publicacion ?? null)
                        ? \Carbon\Carbon::parse($n->fecha_publicacion)
                        : ($n->fecha_publicacion ?? null);
                    $tz = config('app.timezone', 'America/Lima');
                    $autor = $n->autor ?? $n->nombre_usuario ?? null;

                    // Extraer primera imagen si existe
                    $primeraImagen = null;
                    if (!empty($n->imagen)) {
                        $archivos = array_filter(array_map('trim', explode(';', $n->imagen)));
                        foreach ($archivos as $archivo) {
                            $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                $primeraImagen = $archivo;
                                break;
                            }
                        }
                    }
                @endphp
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="card news-card-v2 h-100 hover-lift border-0 shadow-sm">
                        {{-- Imagen destacada o placeholder --}}
                        <div class="news-card-image-wrapper">
                            @if($primeraImagen)
                                <img src="{{ asset('storage/' . ltrim($primeraImagen, '/')) }}"
                                     class="news-card-image"
                                     alt="{{ $n->titulo }}"
                                     loading="lazy">
                            @else
                                <div class="news-card-placeholder">
                                    <i class="bi bi-newspaper display-1 text-white opacity-75"></i>
                                </div>
                            @endif

                            {{-- Badge de categoría (opcional) --}}
                            <div class="news-card-badge">
                                <span class="badge bg-primary">
                                    <i class="bi bi-bookmark-fill me-1"></i>Noticia
                                </span>
                            </div>
                        </div>

                        <div class="card-body d-flex flex-column">
                            {{-- Fecha y autor --}}
                            @if($fechaPublicacion instanceof \Carbon\Carbon)
                                <div class="news-card-meta mb-2">
                                    <small class="text-muted d-flex align-items-center flex-wrap gap-2">
                                        <span>
                                            <i class="bi bi-calendar3 me-1"></i>
                                            {{ $fechaPublicacion->setTimezone($tz)->format('d/m/Y') }}
                                        </span>
                                        @if($autor)
                                            <span class="text-muted">•</span>
                                            <span>
                                                <i class="bi bi-person me-1"></i>
                                                {{ $autor }}
                                            </span>
                                        @endif
                                    </small>
                                </div>
                            @endif

                            {{-- Título --}}
                            <h3 class="h5 mb-2 news-card-title">
                                <a href="{{ route('noticias.show', $n->noticia_id) }}"
                                   class="stretched-link text-decoration-none link-dark">
                                    {{ $n->titulo }}
                                </a>
                            </h3>

                            {{-- Resumen --}}
                            @if(!empty($n->resumen))
                                <p class="text-muted mb-3 flex-grow-1 news-card-excerpt">
                                    {{ Str::limit($n->resumen, 120) }}
                                </p>
                            @elseif(!empty($n->contenido))
                                <p class="text-muted mb-3 flex-grow-1 news-card-excerpt">
                                    {{ Str::limit(strip_tags($n->contenido), 120) }}
                                </p>
                            @endif

                            {{-- Botones de acción --}}
                            <div class="mt-auto d-flex gap-2 justify-content-between align-items-center">
                                <span class="btn btn-sm btn-outline-primary news-card-readmore">
                                    Leer más <i class="bi bi-arrow-right ms-1"></i>
                                </span>

                                @auth
                                @php
                                    $user = auth()->user();
                                    $rolesUsuario = [];
                                    try {
                                        $rolesUsuario = collect(DB::select('EXEC sp_UsuarioRol_ListarPorUsuario ?', [$user->usuario_id ?? $user->id]))
                                            ->pluck('nombre_rol')
                                            ->map(fn($r) => strtolower(trim($r)))
                                            ->toArray();
                                    } catch (\Exception $e) {
                                        \Log::error('Error al obtener roles: ' . $e->getMessage());
                                    }

                                    $tienePermiso = !empty(array_intersect($rolesUsuario, ['editor', 'administrador', 'director']));
                                @endphp

                                {{-- DEBUG: Mostrar info de permisos (ELIMINAR DESPUÉS) --}}
                                @if(config('app.debug'))
                                <small class="text-muted" style="z-index: 10; position: relative;">
                                    Roles: [{{ implode(', ', $rolesUsuario) }}] | Permiso: {{ $tienePermiso ? 'SI' : 'NO' }}
                                </small>
                                @endif

                                @if($tienePermiso)
                                <div class="d-flex gap-1 position-relative" style="z-index: 2;">
                                    <a href="{{ route('noticias.edit', $n->noticia_id) }}"
                                       class="btn btn-sm btn-warning"
                                       onclick="event.stopPropagation();"
                                       title="Editar noticia">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('noticias.destroy', $n->noticia_id) }}"
                                          method="POST"
                                          class="d-inline delete-form"
                                          onclick="event.stopPropagation();">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar noticia">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                @else
                                {{-- DEBUG: Mostrar por qué no tiene permiso --}}
                                @if(config('app.debug'))
                                <small class="text-danger" style="z-index: 10; position: relative;">
                                    Sin permiso
                                </small>
                                @endif
                                @endif
                                @endauth
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $noticias->links() }}
        </div>
    @endif
</div>
@endsection
