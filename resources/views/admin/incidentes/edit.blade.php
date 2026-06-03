@extends('layouts.app')

@section('title', 'Admin — Editar incidente #'.$incidente->id_incidente)

@include('admin._styles')

@section('content')
<div class="admin-page">
    <a href="{{ route('admin.incidentes.index') }}" class="admin-back">
        <i class="bi bi-arrow-left"></i> Volver al listado
    </a>

    <div class="admin-hero mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4><i class="bi bi-pencil-square me-2"></i>Incidente #{{ $incidente->id_incidente }}</h4>
                <p class="mb-0">
                    {{ $incidente->reportante->nombre_completo ?? 'Sin reportante' }}
                    @if($incidente->validador)
                    · Validado por {{ $incidente->validador->nombre_completo }}
                    @endif
                </p>
            </div>
            <a href="{{ route('incidentes.show', $incidente->id_incidente) }}" class="btn btn-sm btn-outline-light">
                <i class="bi bi-eye"></i> Ver público
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger border-0 rounded-3 shadow-sm">
        <strong><i class="bi bi-exclamation-triangle me-1"></i> Revisa los campos:</strong>
        <ul class="mb-0 mt-2 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('admin.incidentes.update', $incidente->id_incidente) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.incidentes._form', ['incidente' => $incidente])

        <div class="admin-sticky-actions">
            <button type="submit" class="btn btn-primary btn-mobile flex-grow-1">
                <i class="bi bi-check-lg me-1"></i> Guardar cambios
            </button>
            <a href="{{ route('incidentes.show', $incidente->id_incidente) }}" class="btn btn-outline-secondary btn-mobile d-none d-sm-inline-flex">Ver detalle</a>
            <a href="{{ route('admin.incidentes.index') }}" class="btn btn-outline-secondary btn-mobile">Cancelar</a>
        </div>
    </form>

    <div class="admin-form-section mt-3 border border-danger border-opacity-25">
        <h6 class="text-danger border-danger border-opacity-25"><i class="bi bi-trash"></i> Zona de peligro</h6>
        <p class="small text-muted mb-3">Elimina permanentemente este incidente y sus archivos asociados.</p>
        <form action="{{ route('admin.incidentes.destroy', $incidente->id_incidente) }}" method="POST"
              onsubmit="return confirm('¿Eliminar permanentemente el incidente #{{ $incidente->id_incidente }}?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-mobile w-100 w-sm-auto">
                <i class="bi bi-trash me-1"></i> Eliminar incidente
            </button>
        </form>
    </div>
</div>
@endsection
