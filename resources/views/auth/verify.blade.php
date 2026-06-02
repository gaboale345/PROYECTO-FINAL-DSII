@extends('layouts.app')

@section('title', 'Verificar correo')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-12 col-md-6">
        <div class="card card-mobile p-4">
            <h3 class="mb-3">Verificar tu cuenta</h3>

            <p>Introduce el código que has recibido en tu correo electrónico para activar tu cuenta.</p>

            <form method="POST" action="{{ route('verification.verify') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="token" class="form-label">Código de verificación</label>
                    <input id="token" type="text" class="form-control @error('token') is-invalid @enderror" name="token" value="{{ old('token') }}" required>
                    @error('token')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-success btn-mobile w-100">Verificar cuenta</button>
            </form>

            <form method="POST" action="{{ route('verification.resend') }}" class="mt-3">
                @csrf
                <input type="hidden" name="email" value="{{ old('email') }}">
                <button type="submit" class="btn btn-outline-secondary btn-mobile w-100">Reenviar código</button>
            </form>

            <div class="mt-3 text-center">
                <a href="{{ route('login') }}">Volver al inicio de sesión</a>
            </div>
        </div>
    </div>
</div>
@endsection
