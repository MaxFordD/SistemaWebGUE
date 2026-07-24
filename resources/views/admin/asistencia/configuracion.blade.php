@extends('layouts.app')

@section('title', 'Configuración de Asistencia')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h3 mb-1 fw-bold"><i class="bi bi-gear me-2 text-primary"></i>Configuración de Asistencia</h1>
        <p class="text-muted mb-0">Define la ventana horaria de registro, el umbral de alertas y el plazo de edición</p>
    </div>

    <div class="d-flex gap-2 mb-4">
        <a href="{{ route('admin.asistencia.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-calendar-check me-1"></i>Registro Diario
        </a>
        <a href="{{ route('admin.asistencia.dias-no-habiles.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-calendar-x me-1"></i>Días no hábiles
        </a>
    </div>

    <div class="card shadow-sm" style="max-width: 640px;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.asistencia.configuracion.update') }}">
                @csrf
                @method('PUT')

                <h6 class="fw-semibold mb-3"><i class="bi bi-clock me-1"></i>Ventana de registro</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Apertura</label>
                        <input type="time" class="form-control" name="hora_apertura"
                               value="{{ old('hora_apertura', substr($config->hora_apertura, 0, 5)) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Cierre</label>
                        <input type="time" class="form-control" name="hora_cierre"
                               value="{{ old('hora_cierre', substr($config->hora_cierre, 0, 5)) }}" required>
                        <div class="form-text">Fuera de este rango no se podrá registrar asistencia por QR.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Límite de tardanza</label>
                        <input type="time" class="form-control" name="hora_limite_tardanza"
                               value="{{ old('hora_limite_tardanza', substr($config->hora_limite_tardanza, 0, 5)) }}" required>
                        <div class="form-text">Después de esta hora, el QR marca Tardanza.</div>
                    </div>
                </div>

                <h6 class="fw-semibold mb-3"><i class="bi bi-exclamation-triangle me-1"></i>Alertas de reincidencia</h6>
                <div class="mb-4">
                    <label class="form-label small fw-semibold">Faltas + tardanzas en el mes para alertar</label>
                    <input type="number" class="form-control" style="max-width:160px" name="umbral_alertas_mes" min="1" max="99"
                           value="{{ old('umbral_alertas_mes', $config->umbral_alertas_mes) }}" required>
                    <div class="form-text">Se resalta al alumno en el historial de sección al alcanzar este total.</div>
                </div>

                <h6 class="fw-semibold mb-3"><i class="bi bi-pencil-square me-1"></i>Edición de registros pasados</h6>
                <div class="mb-4">
                    <label class="form-label small fw-semibold">Días hacia atrás permitidos para editar</label>
                    <input type="number" class="form-control" style="max-width:160px" name="dias_limite_edicion" min="0" max="365"
                           value="{{ old('dias_limite_edicion', $config->dias_limite_edicion) }}" required>
                    <div class="form-text">
                        Superado el plazo, solo los roles con el permiso <strong>"Editar asistencia fuera de plazo"</strong>
                        (asignable en Permisos por Rol) pueden corregir el registro.
                    </div>
                </div>

                @if($config->actualizado_en)
                <p class="text-muted small mb-4">
                    <i class="bi bi-clock-history me-1"></i>Última actualización: {{ \Carbon\Carbon::parse($config->actualizado_en)->format('d/m/Y H:i') }}
                </p>
                @endif

                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save me-2"></i>Guardar Configuración
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
