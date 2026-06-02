@extends('layouts.app')

@section('title', 'Historial de Incidentes')

@section('content')
<style>
    .filter-section {
        background: white;
        border-radius: 16px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .incidente-card {
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
    }
    
    .incidente-card:hover {
        transform: translateX(3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .incidente-card.validado {
        border-left-color: #2ecc71;
    }
    
    .incidente-card.pendiente {
        border-left-color: #f39c12;
    }
    
    .incidente-card.falso {
        border-left-color: #e74c3c;
        opacity: 0.7;
    }
    
    .filter-badge {
        cursor: pointer;
        transition: all 0.2s;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
    }
    
    .filter-badge:hover {
        transform: scale(1.05);
    }
    
    .filter-badge.active {
        background: #3498db !important;
        color: white !important;
    }
    
    .stats-resumen {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 15px;
        margin-bottom: 20px;
        color: white;
    }
    
    .pagination {
        justify-content: center;
        margin-top: 20px;
    }
</style>

<!-- Resumen estadístico -->
<div class="stats-resumen">
    <div class="row text-center">
        <div class="col-4">
            <h5 class="mb-0">{{ $incidentes->total() }}</h5>
            <small>Total</small>
        </div>
        <div class="col-4">
            <h5 class="mb-0 text-success" id="validadosCount">0</h5>
            <small>Validados</small>
        </div>
        <div class="col-4">
            <h5 class="mb-0 text-warning" id="pendientesCount">0</h5>
            <small>Pendientes</small>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="filter-section">
    <h6 class="mb-3">
        <i class="bi bi-funnel-fill"></i> Filtrar incidentes
    </h6>
    
    <div class="row g-3">
        <!-- Filtro por tipo -->
        <div class="col-12 col-md-4">
            <label class="form-label small text-muted">Tipo de incidente</label>
            <select class="form-select form-sm" id="filtroTipo">
                <option value="">📌 Todos los tipos</option>
                @foreach($tiposDelito ?? [] as $tipo)
                <option value="{{ $tipo->id_tipo_delito }}">
                    @php
                        $iconoTipo = '';
                        if(str_contains($tipo->nombre, 'Robo')) $iconoTipo = '🏠';
                        elseif(str_contains($tipo->nombre, 'Asalto')) $iconoTipo = '👊';
                        elseif(str_contains($tipo->nombre, 'Hurto')) $iconoTipo = '🚗';
                        elseif(str_contains($tipo->nombre, 'Violencia')) $iconoTipo = '👥';
                        else $iconoTipo = '📍';
                    @endphp
                    {{ $iconoTipo }} {{ $tipo->nombre }}
                </option>
                @endforeach
            </select>
        </div>
        
        <!-- Filtro por estado -->
        <div class="col-12 col-md-4">
            <label class="form-label small text-muted">Estado</label>
            <select class="form-select form-sm" id="filtroEstado">
                <option value="">📋 Todos los estados</option>
                <option value="pendiente">🟡 Pendiente</option>
                <option value="confirmado">🟢 Confirmado</option>
                <option value="cerrado">⚪ Cerrado</option>
                <option value="fusionado">🔄 Fusionado</option>
            </select>
        </div>
        
        <!-- Filtro por fecha -->
        <div class="col-12 col-md-4">
            <label class="form-label small text-muted">Fecha</label>
            <input type="date" class="form-control form-sm" id="filtroFecha" placeholder="YYYY-MM-DD">
        </div>
    </div>
    
    <!-- Filtros rápidos -->
    <div class="d-flex flex-wrap gap-2 mt-3 pt-2 border-top">
        <span class="filter-badge bg-light text-dark" data-rapido="hoy">
            <i class="bi bi-calendar-day"></i> Hoy
        </span>
        <span class="filter-badge bg-light text-dark" data-rapido="semana">
            <i class="bi bi-calendar-week"></i> Última semana
        </span>
        <span class="filter-badge bg-light text-dark" data-rapido="mes">
            <i class="bi bi-calendar-month"></i> Último mes
        </span>
        <span class="filter-badge bg-danger text-white" id="limpiarFiltros">
            <i class="bi bi-eraser"></i> Limpiar
        </span>
    </div>
</div>

<!-- Lista de incidentes -->
<div id="incidentesList">
    @forelse($incidentes as $incidente)
    <div class="card card-mobile incidente-card mb-3 {{ $incidente->validado ? 'validado' : 'pendiente' }} {{ $incidente->es_falso_reporte ? 'falso' : '' }}" 
         data-id="{{ $incidente->id_incidente }}"
         data-tipo="{{ $incidente->id_tipo_delito }}"
         data-estado="{{ $incidente->estado }}"
         data-validado="{{ $incidente->validado ? '1' : '0' }}"
         data-falso="{{ $incidente->es_falso_reporte ? '1' : '0' }}"
         data-fecha="{{ $incidente->fecha_hora ? $incidente->fecha_hora->format('Y-m-d') : '' }}">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <!-- Badges de estado -->
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        @if($incidente->validado)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Validado
                            </span>
                        @else
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-clock"></i> Pendiente
                            </span>
                        @endif
                        
                        @if($incidente->es_falso_reporte)
                        <span class="badge bg-danger">
                            <i class="bi bi-x-circle"></i> Falso reporte
                        </span>
                        @endif
                        
                        @if($incidente->es_anonimo)
                        <span class="badge bg-secondary">
                            <i class="bi bi-incognito"></i> Anónimo
                        </span>
                        @endif
                        
                        @if($incidente->id_incidente_principal)
                        <span class="badge bg-info">
                            <i class="bi bi-diagram-2"></i> Fusionado
                        </span>
                        @endif
                        
                        <small class="text-muted ms-auto">
                            <i class="bi bi-clock"></i> 
                            {{ $incidente->fecha_hora ? $incidente->fecha_hora->diffForHumans() : 'N/A' }}
                        </small>
                    </div>
                    
                    <!-- Título y tipo -->
                    <h6 class="mb-1">
                        @php
                            $iconoTipo = '';
                            if(str_contains($incidente->tipoDelito->nombre ?? '', 'Robo')) $iconoTipo = '🏠';
                            elseif(str_contains($incidente->tipoDelito->nombre ?? '', 'Asalto')) $iconoTipo = '👊';
                            elseif(str_contains($incidente->tipoDelito->nombre ?? '', 'Hurto')) $iconoTipo = '🚗';
                            elseif(str_contains($incidente->tipoDelito->nombre ?? '', 'Vandalismo')) $iconoTipo = '🎨';
                            elseif(str_contains($incidente->tipoDelito->nombre ?? '', 'Violencia')) $iconoTipo = '👥';
                            elseif(str_contains($incidente->tipoDelito->nombre ?? '', 'Emergencia')) $iconoTipo = '🚑';
                            else $iconoTipo = '📍';
                        @endphp
                        {{ $iconoTipo }} {{ $incidente->tipoDelito->nombre ?? 'Sin categoría' }}
                    </h6>
                    
                    <!-- Descripción -->
                    @if($incidente->descripcion)
                    <p class="small text-muted mb-2">{{ Str::limit($incidente->descripcion, 100) }}</p>
                    @endif
                    
                    <!-- Ubicación y acciones -->
                    <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
                        <small class="text-muted">
                            <i class="bi bi-geo-alt"></i> 
                            <span class="coordinates">{{ number_format($incidente->latitud, 6) }}</span>, 
                            <span class="coordinates">{{ number_format($incidente->longitud, 6) }}</span>
                        </small>
                        <div class="btn-group btn-group-sm">
                            <a href="#" class="btn btn-outline-secondary ver-mapa" data-lat="{{ $incidente->latitud }}" data-lng="{{ $incidente->longitud }}">
                                <i class="bi bi-map"></i> Mapa
                            </a>
                            <a href="{{ route('incidentes.show', $incidente->id_incidente) }}" class="btn btn-outline-primary">
                                <i class="bi bi-eye"></i> Detalles
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center text-muted py-5">
        <i class="bi bi-inbox fs-1"></i>
        <p class="mt-2">No hay incidentes registrados</p>
        <a href="{{ route('incidentes.create') }}" class="btn btn-primary btn-mobile mt-2">
            <i class="bi bi-plus-circle"></i> Reportar primer incidente
        </a>
    </div>
    @endforelse
</div>

<!-- Paginación -->
<div class="d-flex justify-content-center mt-4">
    {{ $incidentes->links() }}
</div>

<!-- Modal para ver mapa -->
<div class="modal fade" id="mapaModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-map"></i> Ubicación del incidente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="modalMap" style="height: 400px;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // Elementos de filtros
    const filtroTipo = document.getElementById('filtroTipo');
    const filtroEstado = document.getElementById('filtroEstado');
    const filtroFecha = document.getElementById('filtroFecha');
    const filtrosRapidos = document.querySelectorAll('[data-rapido]');
    const limpiarBtn = document.getElementById('limpiarFiltros');
    const cards = document.querySelectorAll('.incidente-card');
    
    // Contadores
    function actualizarContadores() {
        let visibleCount = 0;
        let validadosCount = 0;
        let pendientesCount = 0;
        
        cards.forEach(card => {
            if (card.style.display !== 'none') {
                visibleCount++;
                if (card.dataset.validado === '1') validadosCount++;
                if (card.dataset.validado === '0') pendientesCount++;
            }
        });
        
        document.getElementById('validadosCount').textContent = validadosCount;
        document.getElementById('pendientesCount').textContent = pendientesCount;
    }
    
    // Función para aplicar filtros
    function aplicarFiltros() {
        const tipoVal = filtroTipo.value;
        const estadoVal = filtroEstado.value;
        const fechaVal = filtroFecha.value;
        
        cards.forEach(card => {
            let mostrar = true;
            
            if (tipoVal && card.dataset.tipo !== tipoVal) {
                mostrar = false;
            }
            if (estadoVal && card.dataset.estado !== estadoVal) {
                mostrar = false;
            }
            if (fechaVal && card.dataset.fecha !== fechaVal) {
                mostrar = false;
            }
            
            card.style.display = mostrar ? 'block' : 'none';
        });
        
        actualizarContadores();
    }
    
    // Filtros rápidos
    filtrosRapidos.forEach(btn => {
        btn.addEventListener('click', function() {
            const rapido = this.dataset.rapido;
            const hoy = new Date();
            
            filtrosRapidos.forEach(b => b.classList.remove('active', 'bg-primary', 'text-white'));
            this.classList.add('active', 'bg-primary', 'text-white');
            
            if (rapido === 'hoy') {
                filtroFecha.value = hoy.toISOString().split('T')[0];
            } else if (rapido === 'semana') {
                const semanaAtras = new Date();
                semanaAtras.setDate(hoy.getDate() - 7);
                filtroFecha.value = semanaAtras.toISOString().split('T')[0];
            } else if (rapido === 'mes') {
                const mesAtras = new Date();
                mesAtras.setMonth(hoy.getMonth() - 1);
                filtroFecha.value = mesAtras.toISOString().split('T')[0];
            }
            
            aplicarFiltros();
        });
    });
    
    // Limpiar filtros
    limpiarBtn.addEventListener('click', function() {
        filtroTipo.value = '';
        filtroEstado.value = '';
        filtroFecha.value = '';
        filtrosRapidos.forEach(b => b.classList.remove('active', 'bg-primary', 'text-white'));
        cards.forEach(card => card.style.display = 'block');
        actualizarContadores();
    });
    
    // Eventos de cambio
    filtroTipo.addEventListener('change', aplicarFiltros);
    filtroEstado.addEventListener('change', aplicarFiltros);
    filtroFecha.addEventListener('change', aplicarFiltros);
    
    // Inicializar contadores
    actualizarContadores();
    
    // Modal para ver mapa
    let modalMap = null;
    
    document.querySelectorAll('.ver-mapa').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const lat = parseFloat(this.dataset.lat);
            const lng = parseFloat(this.dataset.lng);
            
            const modalElement = document.getElementById('mapaModal');
            const modal = new bootstrap.Modal(modalElement);
            
            modalElement.addEventListener('shown.bs.modal', function() {
                if (modalMap) {
                    modalMap.remove();
                }
                modalMap = L.map('modalMap').setView([lat, lng], 16);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>'
                }).addTo(modalMap);
                L.marker([lat, lng]).addTo(modalMap).bindPopup('Ubicación del incidente').openPopup();
            });
            
            modal.show();
        });
    });
    
    // Función para mostrar toast
    function showToastMessage(message, duration = 3000) {
        let toast = document.getElementById('toastMessage');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'toastMessage';
            toast.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:rgba(0,0,0,0.8);color:white;padding:12px 20px;border-radius:50px;font-size:14px;z-index:1100;display:none;white-space:nowrap;';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.display = 'none';
        }, duration);
    }
</script>
@endpush