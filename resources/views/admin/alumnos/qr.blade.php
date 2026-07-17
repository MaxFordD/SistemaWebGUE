@extends('layouts.app')

@section('title', 'Carnet QR - ' . $alumno->apellidos . ', ' . $alumno->nombres)

@section('content')
<div class="container py-4">
    <div class="no-print mb-3 d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.alumnos.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Imprimir carnet
        </button>
    </div>

    <div class="border rounded-4 shadow-sm p-4" style="border-color:#7a1a0c !important;">
        @include('admin.alumnos.qr-carnet')
    </div>
</div>
@endsection
