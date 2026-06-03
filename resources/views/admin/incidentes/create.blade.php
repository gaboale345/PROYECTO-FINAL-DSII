@extends('layouts.app')

@section('title', 'Admin — Nuevo incidente')

@include('admin._styles')

@section('content')
<div class="admin-page">
    <a href="{{ route('admin.incidentes.index') }}" class="admin-back">
        <i class="bi bi-arrow-left"></i> Volver al listado
    </a>

    <div class="admin-hero mb-3">
        <h4><i class="bi bi-plus-circle me-2"></i>Nuevo incidente</h4>
        <p>Registro manual desde el panel de administración.</p>
    </div>

    @if($errors->any())
    <div class="alert alert-danger border-0 rounded-3 shadow-sm">
        <strong><i class="bi bi-exclamation-triangle me-1"></i> Revisa los campos:</strong>
        <ul class="mb-0 mt-2 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('admin.incidentes.store') }}" method="POST">
        @csrf
        @include('admin.incidentes._form')

        <div class="admin-sticky-actions">
            <button type="submit" class="btn btn-primary btn-mobile flex-grow-1">
                <i class="bi bi-check-lg me-1"></i> Guardar incidente
            </button>
            <a href="{{ route('admin.incidentes.index') }}" class="btn btn-outline-secondary btn-mobile">Cancelar</a>
        </div>
    </form>
</div>
@endsection
