@extends('layouts.app')

@section('title', 'Detalle Documento')

@section('content')
<div class="py-4">
    <h1 class="h3 fw-bold mb-3">📄 Detalle del Documento</h1>

    <a href="{{ route('admin.mesa.index') }}" class="btn btn-secondary mb-3">← Volver</a>

    <div class="card">
        <div class="card-body">
            <p><strong>Remitente:</strong> {{ $doc->remitente }}</p>
            <p><strong>DNI:</strong> {{ $doc->dni ?? 'No especificado' }}</p>
            <p><strong>Correo:</strong> {{ $doc->correo ?? 'No proporcionado' }}</p>
            <p><strong>Asunto:</strong> {{ $doc->asunto }}</p>
            <p><strong>Detalle:</strong> {{ $doc->detalle ?? 'Sin detalle' }}</p>
            <p><strong>Tipo:</strong> {{ $doc->tipo_documento }}</p>
            <p><strong>Estado:</strong> {{ $doc->estado }}</p>
            <p><strong>Fecha de Envío:</strong> {{ \Carbon\Carbon::parse($doc->fecha_envio)->format('d/m/Y H:i') }}</p>

            @if ($doc->archivo)
                <p><strong>Archivo:</strong></p>
                <a href="{{ asset($doc->archivo) }}" class="btn btn-primary" target="_blank">📎 Ver documento</a>
            @else
                <p class="text-muted">No se adjuntó archivo.</p>
            @endif
        </div>
    </div>
</div>
@endsection
