@extends('layouts.app')

@section('title', 'Ingreso')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-12 col-md-5">
        <div class="card card-mobile p-4">
            <h3 class="mb-3">Iniciar sesión</h3>

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">Recordarme</label>
                </div>

                <button type="submit" class="btn btn-primary btn-mobile w-100">Ingresar</button>
            </form>

            <div class="mt-3 text-center">
                <a href="{{ route('register') }}">Crear una cuenta nueva</a>
            </div>
        </div>
    </div>
</div>
@endsection
