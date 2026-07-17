@extends('layouts.app')

@section('title', 'Documentos - Mesa de Partes')

@section('content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-inbox me-2 text-primary"></i>Mesa de Partes</h1>
        <span class="text-muted">{{ $documentos->total() }} documento(s)</span>
    </div>

    {{-- Filtro por estado --}}
    <div class="mb-3 d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.mesa.index') }}"
           class="btn btn-sm {{ $estado === '' ? 'btn-primary' : 'btn-outline-secondary' }}">
            Todos
        </a>
        @foreach($estados as $e)
        <a href="{{ route('admin.mesa.index', ['estado' => $e]) }}"
           class="btn btn-sm {{ $estado === $e ? 'btn-primary' : 'btn-outline-secondary' }}">
            {{ $e }}
            @if($e === 'Pendiente')
                @php $countPendiente = collect(\Illuminate\Support\Facades\DB::select('CALL sp_MesaPartes_Listar()'))->where('estado','Pendiente')->count(); @endphp
                @if($countPendiente > 0)
                    <span class="badge bg-danger ms-1">{{ $countPendiente }}</span>
                @endif
            @endif
        </a>
        @endforeach
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">ID</th>
                            <th>Remitente</th>
                            <th>Asunto</th>
                            <th width="120">Tipo</th>
                            <th width="140">Fecha</th>
                            <th width="160">Estado</th>
                            <th width="100" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($documentos as $d)
                        <tr>
                            <td class="text-muted small font-monospace">{{ $d->documento_id }}</td>
                            <td class="fw-semibold">{{ $d->remitente }}</td>
                            <td>{{ Str::limit($d->asunto, 60) }}</td>
                            <td><span class="badge bg-secondary">{{ $d->tipo_documento }}</span></td>
                            <td class="small text-muted">
                                {{ \Carbon\Carbon::parse($d->fecha_envio)->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <form action="{{ route('admin.mesa.estado', $d->documento_id) }}" method="POST" class="js-auto-submit-form">
                                    @csrf
                                    <select name="estado" class="form-select form-select-sm js-auto-submit">
                                        @foreach($estados as $e)
                                            <option value="{{ $e }}" {{ $d->estado === $e ? 'selected' : '' }}>{{ $e }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('admin.mesa.show', $d->documento_id) }}"
                                       class="btn btn-sm btn-outline-info" title="Ver detalles">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <form action="{{ route('admin.mesa.destroy', $d->documento_id) }}"
                                          method="POST" class="d-inline js-confirmable"
                                          data-confirm="¿Eliminar el documento de {{ $d->remitente }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                No hay documentos{{ $estado ? ' con estado "'.$estado.'"' : '' }}.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($documentos->hasPages())
        <div class="card-footer bg-white">
            {{ $documentos->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script defer src="{{ asset('js/admin-mesa.js') }}"></script>
@endpush
