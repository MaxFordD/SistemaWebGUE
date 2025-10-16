@extends('layouts.app')

@section('title', 'Documentos - Mesa de Partes')

@section('content')
<div class="py-4">
    <h1 class="h3 fw-bold mb-3">Documentos recibidos</h1>

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Remitente</th>
                <th>Asunto</th>
                <th>Tipo</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($documentos as $d)
            <tr>
                <td>{{ $d->documento_id }}</td>
                <td>{{ $d->remitente }}</td>
                <td>{{ $d->asunto }}</td>
                <td>{{ $d->tipo_documento }}</td>
                <td>{{ \Carbon\Carbon::parse($d->fecha_envio)->format('d/m/Y H:i') }}</td>
                <td>
                    <form action="{{ route('admin.mesa.estado', $d->documento_id) }}" method="post" class="js-auto-submit-form">
                        @csrf
                        <select name="estado" class="form-select form-select-sm js-auto-submit">
                            <option {{ $d->estado == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option {{ $d->estado == 'Revisado' ? 'selected' : '' }}>Revisado</option>
                        </select>
                    </form>
                </td>
                <td>
                    <a href="{{ route('admin.mesa.show', $d->documento_id) }}" class="btn btn-sm btn-info">Ver</a>
                    <form action="{{ route('admin.mesa.destroy', $d->documento_id) }}" method="post" class="d-inline js-confirmable" data-confirm="¿Eliminar este documento?">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script defer src="{{ asset('js/admin-mesa.js') }}"></script>
@endpush
