@extends('layouts.app')

@section('title', 'Crear Directivo')

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
                    <h1 class="h3 mb-1 fw-bold">Nuevo Directivo</h1>
                    <p class="text-muted mb-0">Registra un nuevo miembro del comité directivo</p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.comite-directivo.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Nombre Completo --}}
                        <div class="mb-3">
                            <label for="nombre_completo" class="form-label fw-semibold">
                                Nombre Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('nombre_completo') is-invalid @enderror"
                                   id="nombre_completo"
                                   name="nombre_completo"
                                   value="{{ old('nombre_completo') }}"
                                   required
                                   maxlength="200"
                                   placeholder="Ej: Dr. Juan Pérez Rodríguez">
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
                                   value="{{ old('cargo') }}"
                                   required
                                   maxlength="100"
                                   placeholder="Ej: Director General">
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
                                   value="{{ old('grado_cargo') }}"
                                   maxlength="100"
                                   placeholder="Ej: 1° y 2° grado, Todos los grados">
                            @error('grado_cargo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Opcional. Especifica el grado o nivel educativo.
                            </small>
                        </div>

                        {{-- Foto --}}
                        <div class="mb-3">
                            <label for="foto" class="form-label fw-semibold">
                                Fotografía
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
                                      rows="4"
                                      placeholder="Breve reseña profesional del directivo...">{{ old('biografia') }}</textarea>
                            @error('biografia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Opcional. Describe la experiencia y trayectoria del directivo.
                            </small>
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
                                       value="{{ old('orden', 0) }}"
                                       required
                                       min="0"
                                       step="1">
                                @error('orden')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Número para ordenar la visualización (menor primero).
                                </small>
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
                                    <option value="A" {{ old('estado', 'A') == 'A' ? 'selected' : '' }}>Activo</option>
                                    <option value="I" {{ old('estado') == 'I' ? 'selected' : '' }}>Inactivo</option>
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
                                <i class="bi bi-check-circle me-2"></i>Guardar Directivo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
