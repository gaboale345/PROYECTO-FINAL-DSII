@extends('layouts.app')

@section('title', 'Admin — Historial de incidentes')

@include('admin._styles')

@section('content')
<div class="admin-page">
    <div class="admin-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4><i class="bi bi-shield-lock me-2"></i>Administración de incidentes</h4>
            <p>Gestiona el historial completo: crear, editar, validar y eliminar.</p>
        </div>
        <a href="{{ route('admin.incidentes.create') }}" class="btn btn-light btn-mobile text-dark fw-semibold flex-shrink-0">
            <i class="bi bi-plus-lg"></i> Nuevo
        </a>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-6 col-sm-3">
            <div class="admin-stat">
                <div class="num">{{ $stats['total'] }}</div>
                <div class="lbl">Total</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="admin-stat stat-ok">
                <div class="num">{{ $stats['validados'] }}</div>
                <div class="lbl">Validados</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="admin-stat stat-warn">
                <div class="num">{{ $stats['pendientes'] }}</div>
                <div class="lbl">Pendientes</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="admin-stat stat-danger">
                <div class="num">{{ $stats['falsos'] }}</div>
                <div class="lbl">Falsos</div>
            </div>
        </div>
    </div>

    <div class="admin-filters">
        <button class="admin-filters-toggle d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#adminFilters" aria-expanded="false">
            <span><i class="bi bi-funnel me-2"></i>Filtros de búsqueda</span>
            <i class="bi bi-chevron-down"></i>
        </button>
        <div class="collapse d-lg-block" id="adminFilters">
            <div class="admin-filters-body">
                <form method="get" action="{{ route('admin.incidentes.index') }}" class="row g-3">
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label small fw-semibold">Buscar</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="buscar" class="form-control"
                                   value="{{ request('buscar') }}" placeholder="ID, descripción, nombre">
                        </div>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label small fw-semibold">Barrio</label>
                        <select name="id_barrio" class="form-select">
                            <option value="">Todos</option>
                            @foreach($barrios as $b)
                            <option value="{{ $b->id_barrio }}" {{ request('id_barrio') == $b->id_barrio ? 'selected' : '' }}>{{ $b->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="">Todos</option>
                            @foreach($estados as $valor => $etiqueta)
                            <option value="{{ $valor }}" {{ request('estado') === $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label small fw-semibold">Validado</label>
                        <select name="validado" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ request('validado') === '1' ? 'selected' : '' }}>Sí</option>
                            <option value="0" {{ request('validado') === '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label small fw-semibold">Falso</label>
                        <select name="falso" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ request('falso') === '1' ? 'selected' : '' }}>Sí</option>
                            <option value="0" {{ request('falso') === '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label small fw-semibold">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label small fw-semibold">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary btn-mobile flex-grow-1 flex-sm-grow-0">
                            <i class="bi bi-funnel-fill me-1"></i> Aplicar filtros
                        </button>
                        <a href="{{ route('admin.incidentes.index') }}" class="btn btn-outline-secondary btn-mobile">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Tabla escritorio / tablet horizontal --}}
    <div class="admin-table-wrap d-none d-lg-block">
        <div class="table-responsive">
            <table class="table admin-table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Barrio</th>
                        <th>Estado</th>
                        <th>Etiquetas</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incidentes as $inc)
                    <tr class="{{ $inc->es_falso_reporte ? 'row-falso' : '' }}">
                        <td><span class="fw-bold text-primary">#{{ $inc->id_incidente }}</span></td>
                        <td class="text-nowrap small">{{ $inc->fecha_hora?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td>{{ Str::limit($inc->tipoDelito->nombre ?? '—', 28) }}</td>
                        <td class="small">{{ $inc->reportante?->barrio?->nombre ?? '—' }}</td>
                        <td><span class="badge admin-badge-estado bg-secondary">{{ $inc->estado }}</span></td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @if($inc->validado)<span class="badge bg-success">Validado</span>@endif
                                @if($inc->es_falso_reporte)<span class="badge bg-danger">Falso</span>@endif
                                @if($inc->incidentes_fusionados_count > 0)<span class="badge bg-info text-dark">{{ $inc->incidentes_fusionados_count }} fusion.</span>@endif
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="admin-actions d-inline-flex gap-1">
                                <a href="{{ route('incidentes.show', $inc->id_incidente) }}" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('admin.incidentes.edit', $inc->id_incidente) }}" class="btn btn-sm btn-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('admin.incidentes.destroy', $inc->id_incidente) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar #{{ $inc->id_incidente }}?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="admin-empty">
                                <i class="bi bi-inbox d-block mb-2"></i>
                                No hay incidentes con estos filtros.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tarjetas móvil y tablet --}}
    <div class="d-lg-none">
        @forelse($incidentes as $inc)
        @php
            $cardClass = $inc->es_falso_reporte ? 'is-falso' : ($inc->validado ? 'is-validado' : 'is-pendiente');
        @endphp
        <article class="admin-inc-card {{ $cardClass }}">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                <div>
                    <span class="fw-bold fs-5 text-primary">#{{ $inc->id_incidente }}</span>
                    <div class="small text-muted mt-1">
                        <i class="bi bi-clock"></i> {{ $inc->fecha_hora?->format('d/m/Y H:i') ?? '—' }}
                    </div>
                </div>
                <span class="badge admin-badge-estado bg-secondary">{{ $inc->estado }}</span>
            </div>
            <p class="mb-1 fw-semibold small">{{ $inc->tipoDelito->nombre ?? 'Sin tipo' }}</p>
            <p class="mb-2 small text-muted">
                <i class="bi bi-geo-alt"></i> {{ $inc->reportante?->barrio?->nombre ?? 'Barrio desconocido' }}
            </p>
            <div class="d-flex flex-wrap gap-1 mb-3">
                @if($inc->validado)<span class="badge bg-success">Validado</span>@endif
                @if($inc->es_falso_reporte)<span class="badge bg-danger">Falso</span>@endif
                @if($inc->incidentes_fusionados_count > 0)<span class="badge bg-info text-dark">{{ $inc->incidentes_fusionados_count }} fusionados</span>@endif
            </div>
            <div class="row g-2 admin-actions">
                <div class="col-4">
                    <a href="{{ route('incidentes.show', $inc->id_incidente) }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-eye"></i><span class="d-none d-sm-inline ms-1">Ver</span>
                    </a>
                </div>
                <div class="col-4">
                    <a href="{{ route('admin.incidentes.edit', $inc->id_incidente) }}" class="btn btn-primary w-100">
                        <i class="bi bi-pencil"></i><span class="d-none d-sm-inline ms-1">Editar</span>
                    </a>
                </div>
                <div class="col-4">
                    <form action="{{ route('admin.incidentes.destroy', $inc->id_incidente) }}" method="POST"
                          onsubmit="return confirm('¿Eliminar #{{ $inc->id_incidente }}?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-trash"></i><span class="d-none d-sm-inline ms-1">Borrar</span>
                        </button>
                    </form>
                </div>
            </div>
        </article>
        @empty
        <div class="admin-empty card card-mobile">
            <i class="bi bi-inbox d-block mb-2"></i>
            <p class="mb-0">No hay incidentes con estos filtros.</p>
        </div>
        @endforelse
    </div>

    @if($incidentes->hasPages())
    <div class="mt-3 mb-2">{{ $incidentes->links() }}</div>
    @endif

    <a href="{{ route('incidentes.index', ['alcance' => 'todos']) }}" class="admin-back">
        <i class="bi bi-arrow-left"></i> Volver al historial público
    </a>
</div>
@endsection
