@extends('layouts.app')

@section('title', 'LOGIN')


@section('content')
<div class="login-container d-flex flex-column flex-md-row align-items-stretch">

    <!-- Lado izquierdo -->
    <div class="login-brand d-flex flex-column justify-content-center align-items-center text-center p-3 p-md-4">
        <img src="{{ asset('images/INSIGNIA G.U.E..png') }}" alt="Logo Institución" class="logo mb-3">
        <h2>Institución Educativa</h2>
        <H3>JOSE FAUSTINO SANCHEZ CARRION</H3>
        <p class="mb-0">Plataforma Administrativa</p>
    </div>

    <!-- Lado derecho -->
    <div class="login-form d-flex flex-column justify-content-center p-3 p-md-4 flex-grow-1">
        <div class="card shadow border-0 mx-auto" style="max-width: 420px;">
            <div class="card-body p-4">
                <h3 class="text-center mb-3">Iniciar Sesión</h3>

                @if (session('error'))
                    <div class="alert alert-danger text-center">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" id="loginForm" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="nombre_usuario" class="form-label">Nombre de usuario</label>
                        <input 
                            type="text" 
                            class="form-control @error('nombre_usuario') is-invalid @enderror" 
                            id="nombre_usuario" 
                            name="nombre_usuario" 
                            value="{{ old('nombre_usuario') }}" 
                            required 
                            autofocus
                            placeholder="Ingrese su usuario">
                        @error('nombre_usuario')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <input 
                                type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password" 
                                required
                                placeholder="••••••••">
                            <button type="button" class="btn btn-outline-secondary" id="passwordToggle" aria-label="Mostrar contraseña">
                                <i class="bi bi-eye"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login w-100 py-2 mt-2" id="submitBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                        Ingresar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- CSS específico del login -->
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/login.js') }}"></script>
@endpush
