@extends('layouts.app')

@section('title', 'Detalle Documento')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 fw-bold mb-0">📄 Detalle del Documento</h1>
        <a href="{{ route('admin.mesa.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver
        </a>
    </div>

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
                <p><strong>Archivos adjuntos:</strong></p>
                @php
                    $archivos = array_filter(array_map('trim', explode(';', $doc->archivo)));
                @endphp
                <div class="d-flex flex-wrap gap-2">
                    @foreach($archivos as $archivo)
                        @php
                            $nombreArchivo = basename(str_replace('/storage/', '', $archivo));
                            $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
                            $esImagen = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        @endphp
                        <a href="{{ asset($archivo) }}" class="btn btn-primary btn-sm" target="_blank" rel="noopener">
                            @if($esImagen)
                                <i class="bi bi-image me-2"></i>
                            @elseif($extension === 'pdf')
                                <i class="bi bi-file-pdf me-2"></i>
                            @elseif(in_array($extension, ['doc', 'docx']))
                                <i class="bi bi-file-word me-2"></i>
                            @else
                                <i class="bi bi-paperclip me-2"></i>
                            @endif
                            {{ $nombreArchivo }}
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-muted"><i class="bi bi-info-circle me-2"></i>No se adjuntó archivo.</p>
            @endif
        </div>
    </div>
</div>
@endsection
