@extends('layouts.app')

@section('title', 'Registro de cuenta')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-12 col-md-6">
        <div class="card card-mobile p-4">
            <h3 class="mb-3">Crear cuenta de vecino</h3>
            <p class="text-muted small">Registro simple para adultos mayores y familias de Santa Cruz.</p>

            <form method="POST" action="{{ route('register.post') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Nombre completo</label>
                    <input id="name" type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono (WhatsApp)</label>
                    <input id="telefono" type="tel" class="form-control form-control-lg @error('telefono') is-invalid @enderror" name="telefono" value="{{ old('telefono') }}" placeholder="5917XXXXXXX">
                    @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="id_barrio" class="form-label">Tu barrio</label>
                    <select id="id_barrio" name="id_barrio" class="form-select form-select-lg @error('id_barrio') is-invalid @enderror">
                        <option value="">Selecciona tu barrio</option>
                        @foreach($barrios as $barrio)
                        <option value="{{ $barrio->id_barrio }}" {{ old('id_barrio') == $barrio->id_barrio ? 'selected' : '' }}>
                            {{ $barrio->nombre }} ({{ $barrio->distrito }})
                        </option>
                        @endforeach
                    </select>
                    @error('id_barrio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                    <input id="password_confirmation" type="password" class="form-control form-control-lg" name="password_confirmation" required>
                </div>

                <button type="submit" class="btn btn-primary btn-mobile w-100 btn-lg">Registrar y enviar código</button>
            </form>

            <div class="mt-3 text-center">
                <a href="{{ route('login') }}">¿Ya tienes cuenta? Iniciar sesión</a>
            </div>
        </div>
    </div>
</div>
@endsection
