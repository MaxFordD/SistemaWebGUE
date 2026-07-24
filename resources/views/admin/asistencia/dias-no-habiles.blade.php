@extends('layouts.app')

@section('title', 'Días no hábiles')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1 fw-bold"><i class="bi bi-calendar-x me-2 text-primary"></i>Días no hábiles</h1>
            <p class="text-muted mb-0">Feriados y fechas sin clases: no se podrá registrar asistencia en ellas</p>
        </div>
        <a href="{{ route('admin.asistencia.configuracion.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-gear me-1"></i>Configuración de Asistencia
        </a>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-plus-circle me-1"></i>Registrar día no hábil
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.asistencia.dias-no-habiles.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Fecha</label>
                            <input type="date" class="form-control" name="fecha" value="{{ old('fecha') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Motivo</label>
                            <input type="text" class="form-control" name="motivo" maxlength="150"
                                   placeholder="Ej: Feriado - Día de la Independencia" value="{{ old('motivo') }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save me-1"></i>Registrar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="bi bi-list-ul me-1"></i>Días registrados</span>
                    <form method="GET" class="d-flex gap-2 align-items-center">
                        <input type="number" class="form-control form-control-sm" style="width:100px" name="año"
                               value="{{ $año }}" min="2020" max="2099">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Ver</button>
                    </form>
                </div>
                <div class="card-body p-0">
                    @if($dias->isEmpty())
                        <div class="alert alert-info m-3 mb-0">
                            <i class="bi bi-info-circle me-2"></i>No hay días no hábiles registrados para {{ $año }}.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Motivo</th>
                                        <th class="text-center" width="70">Quitar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dias as $d)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ \Carbon\Carbon::parse($d->fecha)->format('d/m/Y') }}</div>
                                            <div class="small text-muted text-capitalize">{{ \Carbon\Carbon::parse($d->fecha)->locale('es')->isoFormat('dddd') }}</div>
                                        </td>
                                        <td>{{ $d->motivo }}</td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('admin.asistencia.dias-no-habiles.destroy', $d->dia_no_habil_id) }}"
                                                  onsubmit="return confirm('¿Quitar este día no hábil?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Quitar">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-white text-muted small">
                    {{ $dias->count() }} día(s) registrado(s) en {{ $año }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
