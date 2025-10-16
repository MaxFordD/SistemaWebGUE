@extends('layouts.app')

@section('title', 'Noticias')
@section('body_class', 'waves-compact')

@section('content')
<div class="py-3">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h2 fw-bold mb-0">Noticias</h1>
        @role('Editor','Administrador','Director')
            <a class="btn btn-sm btn-primary" href="{{ route('noticias.create') }}">+ Publicar</a>
        @endrole
    </div>

    @if($noticias->isEmpty())
        <div class="alert alert-secondary">No hay noticias aún.</div>
    @else
        <div class="row g-3 g-md-4">
            @foreach($noticias as $n)
                @php
                    $fechaPublicacion = is_string($n->fecha_publicacion ?? null)
                        ? \Carbon\Carbon::parse($n->fecha_publicacion)
                        : ($n->fecha_publicacion ?? null);
                    $tz = config('app.timezone', 'America/Lima');
                    $autor = $n->autor ?? $n->nombre_usuario ?? null;
                @endphp
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="card news-card h-100 hover-lift">
                        <div class="card-body d-flex flex-column">
                            <h3 class="h5 mb-2">
                                <a href="{{ route('noticias.show', $n->noticia_id) }}" class="stretched-link text-decoration-none link-dark">
                                    {{ $n->titulo }}
                                </a>
                            </h3>
                            @if($fechaPublicacion instanceof \Carbon\Carbon)
                                <small class="text-muted mb-2 d-block">
                                    {{ $fechaPublicacion->setTimezone($tz)->format('d/m/Y H:i') }}
                                    @if($autor) — {{ $autor }} @endif
                                </small>
                            @endif
                            @if(!empty($n->resumen))
                                <p class="text-muted mb-0 flex-grow-1">{{ Str::limit($n->resumen, 140) }}</p>
                            @endif
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
