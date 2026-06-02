@extends('layouts.app')

@section('title', 'Detalles del Incidente')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8">
        <div class="card card-mobile">
            <div class="card-body p-4">
                <!-- Botón volver -->
                <a href="{{ route('incidentes.index') }}" class="btn btn-link text-decoration-none mb-3">
                    <i class="bi bi-arrow-left"></i> Volver al listado
                </a>
                
                <h4 class="mb-3">
                    <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                    Detalles del incidente
                </h4>
                
                <!-- Estado -->
                <div class="mb-3">
                    <span class="badge bg-{{ $incidente->validado ? 'success' : 'warning' }} fs-6 p-2">
                        {{ $incidente->validado ? '✓ Validado' : '⏳ Pendiente' }}
                    </span>
                    @if($incidente->es_falso_reporte)
                    <span class="badge bg-danger fs-6 p-2 ms-2">
                        <i class="bi bi-x-circle"></i> Falso reporte
                    </span>
                    @endif
                </div>
                
                <!-- Tipo de incidente -->
                <div class="mb-3">
                    <label class="text-muted small">Tipo de incidente</label>
                    <p class="fw-bold mb-0">{{ $incidente->tipoDelito->nombre ?? 'Sin tipo' }}</p>
                </div>
                
                <!-- Descripción -->
                <div class="mb-3">
                    <label class="text-muted small">Descripción</label>
                    <p class="mb-0">{{ $incidente->descripcion ?: 'Sin descripción' }}</p>
                </div>
                
                <!-- Ubicación -->
                <div class="mb-3">
                    <label class="text-muted small">Ubicación</label>
                    <p class="mb-0">
                        <i class="bi bi-geo-alt"></i>
                        Lat: {{ $incidente->latitud }}, Lng: {{ $incidente->longitud }}
                    </p>
                </div>
                
                <!-- Fecha -->
                <div class="mb-3">
                    <label class="text-muted small">Fecha y hora</label>
                    <p class="mb-0">
                        {{ $incidente->fecha_hora ? $incidente->fecha_hora->format('d/m/Y H:i:s') : 'N/A' }}
                    </p>
                </div>
                
                <!-- Reportante -->
                <div class="mb-3">
                    <label class="text-muted small">Reportado por</label>
                    <p class="mb-0">
                        @if($incidente->es_anonimo)
                            <i class="bi bi-incognito"></i> Anónimo
                        @else
                            {{ $incidente->reportante->nombre_completo ?? 'Usuario desconocido' }}
                        @endif
                    </p>
                </div>
                
                <!-- Validación -->
                @if($incidente->validado)
                <div class="mb-3">
                    <label class="text-muted small">Validado por</label>
                    <p class="mb-0">
                        {{ $incidente->validador->nombre_completo ?? 'Desconocido' }}
                        <small class="text-muted">
                            el {{ $incidente->fecha_validacion ? $incidente->fecha_validacion->format('d/m/Y H:i') : 'N/A' }}
                        </small>
                    </p>
                </div>
                @endif
                
                <!-- Incidentes fusionados -->
                @if($fusionados->count() > 0)
                <div class="mb-3">
                    <label class="text-muted small">Incidentes relacionados</label>
                    @foreach($fusionados as $fusionado)
                    <p class="mb-0">
                        <a href="{{ route('incidentes.show', $fusionado->id_incidente) }}">
                            #{{ $fusionado->id_incidente }} - {{ $fusionado->tipoDelito->nombre ?? 'Sin tipo' }}
                        </a>
                    </p>
                    @endforeach
                </div>
                @endif
                
                <!-- Botones de acción (solo para admins) -->
                @if(!$incidente->validado && !$incidente->es_falso_reporte)
                <div class="d-flex gap-2 mt-4">
                    <form action="{{ route('incidentes.validar', $incidente->id_incidente) }}" method="POST" class="flex-grow-1">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Validar incidente
                        </button>
                    </form>
                    <form action="{{ route('incidentes.marcar-falso', $incidente->id_incidente) }}" method="POST" class="flex-grow-1">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-x-circle"></i> Marcar como falso
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection