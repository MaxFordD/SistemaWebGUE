@extends('layouts.app')

@section('title', 'Bitácora del Sistema')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Bitácora del Sistema</h1>
            <p class="text-muted mb-0">Registro de acciones realizadas por los usuarios</p>
        </div>
    </div>

    {{-- Buscador --}}
    <form method="GET" action="{{ route('admin.bitacora.index') }}" class="mb-4">
        <div class="input-group" style="max-width: 420px;">
            <input type="text" name="buscar" class="form-control"
                   placeholder="Buscar por usuario o acción..."
                   value="{{ $buscar }}">
            <button class="btn btn-outline-secondary" type="submit">
                <i class="bi bi-search"></i>
            </button>
            @if($buscar)
                <a href="{{ route('admin.bitacora.index') }}" class="btn btn-outline-danger" title="Limpiar">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <span class="fw-semibold">
                <i class="bi bi-list-ul me-2 text-primary"></i>
                {{ $bitacora->total() }} registro(s)
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">#</th>
                            <th width="160">Usuario</th>
                            <th>Acción</th>
                            <th width="170">Fecha y hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bitacora as $i => $reg)
                        <tr>
                            <td class="text-center text-muted small">{{ $bitacora->firstItem() + $i }}</td>
                            <td>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-person me-1"></i>{{ $reg->nombre_usuario }}
                                </span>
                            </td>
                            <td>{{ $reg->accion }}</td>
                            <td class="text-muted small font-monospace">
                                {{ \Carbon\Carbon::parse($reg->fecha)->format('d/m/Y H:i:s') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                No hay registros en la bitácora.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($bitacora->hasPages())
        <div class="card-footer bg-white">
            {{ $bitacora->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
