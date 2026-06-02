@extends('layouts.app')

@section('title', 'Alertas Predictivas')

@section('content')
<div class="card card-mobile">
    <div class="card-body">
        <h5 class="mb-3"><i class="bi bi-bell-fill text-warning"></i> Alertas de tu sector</h5>
        <p class="text-muted small">Predicciones generadas por IA con datos históricos. Evita calles de alto riesgo después de las 20:00.</p>

        @forelse($alertas as $alerta)
        <div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="badge bg-{{ $alerta->nivel_riesgo === 'critico' ? 'danger' : ($alerta->nivel_riesgo === 'alto' ? 'warning' : 'info') }}">
                        {{ strtoupper($alerta->nivel_riesgo) }}
                    </span>
                    <h6 class="mt-2 mb-1">{{ $alerta->barrio->nombre ?? 'Barrio' }}</h6>
                    <p class="mb-1 small">{{ $alerta->mensaje }}</p>
                    <small class="text-muted">
                        <i class="bi bi-clock"></i> {{ $alerta->franja_horaria }}
                        · {{ round($alerta->probabilidad * 100) }}% probabilidad
                    </small>
                </div>
                <i class="bi bi-shield-exclamation fs-3 text-danger"></i>
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-5">
            <i class="bi bi-shield-check fs-1 text-success"></i>
            <p class="mt-2">No hay alertas activas en tu sector. ¡Buenas noticias!</p>
        </div>
        @endforelse

        {{ $alertas->links() }}
    </div>
</div>
@endsection
