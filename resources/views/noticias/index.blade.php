@extends('layouts.app')

@section('title', 'Noticias')
@section('body_class', 'waves-compact')

@push('scripts')
<script src="{{ asset('js/confirm-delete.js') }}"></script>
@endpush

@section('content')
<div class="py-3">
   
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h2 fw-bold mb-0">
            <i class="bi bi-newspaper me-2 text-primary"></i>Noticias
        </h1>
        @permission('noticias.admin')
            <a class="btn btn-sm btn-primary" href="{{ route('noticias.create') }}">
                <i class="bi bi-plus-circle me-1"></i>Publicar
            </a>
        @endpermission
    </div>

    {{-- Buscador y filtro año --}}
    <form method="GET" action="{{ route('noticias.index') }}" class="mb-4">
        <div class="d-flex gap-3 flex-wrap align-items-center">
            <div class="search-bar-noticias" style="max-width: 380px;">
                <button class="search-bar-noticias-btn" type="submit" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>
                <input type="text"
                       name="buscar"
                       class="form-control"
                       placeholder="Buscar noticias..."
                       value="{{ request('buscar') }}">
                @if(request('buscar'))
                    <a href="{{ route('noticias.index') }}" class="search-bar-noticias-btn" title="Limpiar búsqueda">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
            <select name="año" class="form-select" style="max-width:120px;" onchange="this.form.submit()">
                <option value="">AÑO</option>
                @for($y = date('Y'); $y >= 2022; $y--)
                    <option value="{{ $y }}" {{ request('año') == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        @if(request('buscar') || request('año'))
            <small class="text-muted mt-2 d-block">
                {{ $noticias->total() }} resultado(s)
                @if(request('buscar')) para "<strong>{{ request('buscar') }}</strong>"@endif
                @if(request('año')) del año <strong>{{ request('año') }}</strong>@endif
            </small>
        @endif
    </form>

    @if($noticias->isEmpty())
        <div class="alert alert-secondary d-flex align-items-center">
            <i class="bi bi-info-circle fs-4 me-3"></i>
            <div>No hay noticias publicadas aún.</div>
        </div>
    @else
        <div class="row g-3 g-md-4">
            @foreach($noticias as $n)
                <div class="col-12 col-md-6 col-lg-4">
                    @include('noticias._card', ['noticia' => $n])
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $noticias->links() }}
        </div>
    @endif
</div>
@endsection
