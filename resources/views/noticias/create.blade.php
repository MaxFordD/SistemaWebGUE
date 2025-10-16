@extends('layouts.app')

@section('title', 'Crear Noticia')

@section('content')
<div class="container">
    <h1 class="mb-3">Crear Noticia</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Revisa los campos:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('noticias.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="titulo" class="form-label">Título</label>
            <input type="text" name="titulo" id="titulo" class="form-control"
                   value="{{ old('titulo') }}" maxlength="200" required>
        </div>

        <div class="mb-3">
            <label for="contenido" class="form-label">Contenido</label>
            <textarea name="contenido" id="contenido" class="form-control" rows="6" required>{{ old('contenido') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="imagen" class="form-label">Imagen (opcional)</label>
            <input type="file" name="imagen" id="imagen" class="form-control" accept="image/*">
            <div class="form-text">Máx. 2 MB</div>
        </div>

        <button type="submit" class="btn btn-primary">Publicar noticia</button>
        <a href="{{ route('noticias.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
