{{-- Tarjeta de noticia reutilizable (usada en noticias/index.blade.php y en la home) --}}
@php
    $n = $noticia;
@endphp
<article class="card news-card-v2 h-100 hover-lift border-0 shadow-sm">
    {{-- Imagen destacada o placeholder --}}
    <div class="news-card-image-wrapper">
        @if($n->primera_imagen)
            <img src="{{ asset('storage/' . ltrim($n->primera_imagen, '/')) }}"
                 class="news-card-image"
                 alt="{{ $n->titulo }}"
                 loading="lazy"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="news-card-placeholder" style="display: none;">
                <i class="bi bi-newspaper display-1 text-white opacity-75"></i>
            </div>
        @elseif(count($n->videos ?? []) > 0)
            @php
                $primerVideo = $n->videos[0];
                $ytMatch = preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $primerVideo, $ytM);
            @endphp
            @if($ytMatch)
                <img src="https://img.youtube.com/vi/{{ $ytM[1] }}/hqdefault.jpg"
                     class="news-card-image"
                     alt="{{ $n->titulo }}"
                     loading="lazy"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="news-card-placeholder" style="display:none;">
                    <i class="bi bi-play-circle display-1 text-white opacity-75"></i>
                </div>
            @else
                <div class="news-card-placeholder">
                    <i class="bi bi-play-circle display-1 text-white opacity-75"></i>
                </div>
            @endif
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
        @if($n->fecha_formateada ?? null)
            <div class="news-card-meta mb-2">
                <small class="text-muted d-flex align-items-center flex-wrap gap-2">
                    <span>
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ $n->fecha_formateada }}
                    </span>
                    @if($n->autor ?? $n->nombre_usuario ?? null)
                        <span class="text-muted">•</span>
                        <span>
                            <i class="bi bi-person me-1"></i>
                            {{ $n->autor ?? $n->nombre_usuario }}
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
                @php
                    $excerpt = html_entity_decode($n->contenido ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $excerpt = html_entity_decode($excerpt, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $excerpt = strip_tags($excerpt);
                @endphp
                {{ Str::limit($excerpt, 120) }}
            </p>
        @endif

        {{-- Botones de acción --}}
        <div class="mt-auto d-flex gap-2 justify-content-between align-items-center">
            <a href="{{ route('noticias.show', $n->noticia_id) }}"
               class="btn btn-sm btn-outline-primary news-card-readmore"
               style="position: relative; z-index: 2;">
                Leer más <i class="bi bi-arrow-right ms-1"></i>
            </a>

            @role('Editor','Administrador','Director')
            <div class="d-flex gap-2 position-relative" style="z-index: 2;">
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
            @endrole
        </div>
    </div>
</article>
