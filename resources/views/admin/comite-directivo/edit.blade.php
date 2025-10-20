@extends('layouts.app')

@section('title', 'Editar Directivo')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-comite-directivo.css') }}" />
@endpush

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('admin.comite-directivo.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h1 class="h3 mb-1 fw-bold">Editar Directivo</h1>
                    <p class="text-muted mb-0">Modifica la información del miembro del comité directivo</p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.comite-directivo.update', $directivo->directivo_id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Nombre Completo --}}
                        <div class="mb-3">
                            <label for="nombre_completo" class="form-label fw-semibold">
                                Nombre Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('nombre_completo') is-invalid @enderror"
                                   id="nombre_completo"
                                   name="nombre_completo"
                                   value="{{ old('nombre_completo', $directivo->nombre_completo) }}"
                                   required
                                   maxlength="200">
                            @error('nombre_completo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Cargo --}}
                        <div class="mb-3">
                            <label for="cargo" class="form-label fw-semibold">
                                Cargo <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('cargo') is-invalid @enderror"
                                   id="cargo"
                                   name="cargo"
                                   value="{{ old('cargo', $directivo->cargo) }}"
                                   required
                                   maxlength="100">
                            @error('cargo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Grado a Cargo --}}
                        <div class="mb-3">
                            <label for="grado_cargo" class="form-label fw-semibold">
                                Grado a Cargo
                            </label>
                            <input type="text"
                                   class="form-control @error('grado_cargo') is-invalid @enderror"
                                   id="grado_cargo"
                                   name="grado_cargo"
                                   value="{{ old('grado_cargo', $directivo->grado_cargo) }}"
                                   maxlength="100">
                            @error('grado_cargo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Foto Actual --}}
                        @if($directivo->foto)
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Fotografía Actual</label>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ asset('storage/' . $directivo->foto) }}"
                                     alt="{{ $directivo->nombre_completo }}"
                                     class="rounded"
                                     style="width: 100px; height: 100px; object-fit: cover;">
                                <div>
                                    <p class="mb-1 small text-muted">
                                        Si subes una nueva foto, esta será reemplazada.
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Nueva Foto --}}
                        <div class="mb-3">
                            <label for="foto" class="form-label fw-semibold">
                                {{ $directivo->foto ? 'Cambiar Fotografía' : 'Fotografía' }}
                            </label>
                            <input type="file"
                                   class="form-control @error('foto') is-invalid @enderror"
                                   id="foto"
                                   name="foto"
                                   accept="image/jpeg,image/jpg,image/png">
                            @error('foto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Opcional. Solo JPG, JPEG o PNG. Máximo 2MB.
                            </small>
                        </div>

                        {{-- Biografía --}}
                        <div class="mb-3">
                            <label for="biografia" class="form-label fw-semibold">
                                Biografía
                            </label>
                            <textarea class="form-control @error('biografia') is-invalid @enderror"
                                      id="biografia"
                                      name="biografia"
                                      rows="4">{{ old('biografia', $directivo->biografia) }}</textarea>
                            @error('biografia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            {{-- Orden --}}
                            <div class="col-md-6 mb-3">
                                <label for="orden" class="form-label fw-semibold">
                                    Orden de Visualización <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                       class="form-control @error('orden') is-invalid @enderror"
                                       id="orden"
                                       name="orden"
                                       value="{{ old('orden', $directivo->orden) }}"
                                       required
                                       min="0"
                                       step="1">
                                @error('orden')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Estado --}}
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label fw-semibold">
                                    Estado <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('estado') is-invalid @enderror"
                                        id="estado"
                                        name="estado"
                                        required>
                                    <option value="A" {{ old('estado', $directivo->estado) == 'A' ? 'selected' : '' }}>Activo</option>
                                    <option value="I" {{ old('estado', $directivo->estado) == 'I' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('admin.comite-directivo.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Actualizar Directivo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
