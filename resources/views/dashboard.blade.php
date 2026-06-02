@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<style>
    /* Estilos específicos del dashboard */
    .risk-card {
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 20px;
        transition: transform 0.2s;
    }
    
    .risk-card:hover {
        transform: translateY(-2px);
    }
    
    .selector-barrio {
        background: white;
        border-radius: 12px;
        padding: 8px 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    #map {
        height: 300px;
        border-radius: 16px;
        z-index: 1;
    }
    
    @media (min-width: 768px) {
        #map {
            height: 400px;
        }
    }
    
    .legend {
        background: white;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 2px;
        display: inline-block;
        margin-right: 4px;
    }
    
    .stat-circle {
        width: 60px;
        height: 60px;
        border-radius: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: bold;
    }
    
    .trend-up { color: #e74c3c; }
    .trend-down { color: #2ecc71; }
</style>

<!-- Selector de barrio -->
<div class="selector-barrio d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-geo-alt-fill text-primary"></i>
        <span class="fw-semibold">Barrio:</span>
        <select id="barrioSelector" class="form-select form-select-sm w-auto">
            @forelse($barrios as $barrio)
            <option value="{{ $barrio->id_barrio }}" {{ $barrioActual && $barrioActual->id_barrio == $barrio->id_barrio ? 'selected' : '' }}>
                {{ $barrio->nombre }}
            </option>
            @empty
            <option value="">Sin barrios configurados</option>
            @endforelse
        </select>
    </div>
    <button id="verMapaBtn" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-map"></i> Ver mapa
    </button>
</div>

<!-- Cards de resumen -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="risk-card bg-{{ $nivelRiesgo['clase'] }} bg-opacity-10">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <small class="text-muted">Riesgo actual</small>
                    <h3 class="mb-0 text-{{ $nivelRiesgo['clase'] }}">{{ $nivelRiesgo['texto'] }}</h3>
                </div>
                <i class="bi {{ $nivelRiesgo['icono'] }} fs-1 text-{{ $nivelRiesgo['clase'] }}"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="risk-card bg-primary bg-opacity-10">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <small class="text-muted">Incidentes (30 días)</small>
                    <h3 class="mb-0 text-primary">{{ $totalIncidentes30d }}</h3>
                </div>
                <i class="bi bi-bar-chart-steps fs-1 text-primary"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="risk-card bg-warning bg-opacity-10">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <small class="text-muted">Zonas críticas</small>
                    <h3 class="mb-0 text-warning">{{ $zonasCriticas }}</h3>
                </div>
                <i class="bi bi-exclamation-triangle fs-1 text-warning"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="risk-card bg-success bg-opacity-10">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <small class="text-muted">Participación vecinal</small>
                    <h3 class="mb-0 text-success">{{ $participacionVecinal }}</h3>
                </div>
                <i class="bi bi-people fs-1 text-success"></i>
            </div>
        </div>
    </div>
</div>

<!-- Tendencia y mapa -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="card card-mobile">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up text-info"></i> Tendencia de incidentes
                    </h6>
                    <span class="badge bg-{{ $tendencia >= 0 ? 'danger' : 'success' }}">
                        <i class="bi bi-arrow-{{ $tendencia >= 0 ? 'up' : 'down' }}"></i>
                        {{ abs($tendencia) }}% vs. periodo anterior
                    </span>
                </div>
                <div>
                    <canvas id="tendenciaChart" height="150"></canvas>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="bi bi-arrow-{{ $tendencia >= 0 ? 'up' : 'down' }}"></i>
                        {{ abs($tendencia) }}% este mes
                    </small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card card-mobile">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                    <h6 class="mb-0">
                        <i class="bi bi-map text-danger"></i> Mapa predictivo de riesgo
                    </h6>
                    <div class="legend d-none d-md-flex">
                        <div><span class="legend-color" style="background:#e74c3c;"></span> Alto</div>
                        <div><span class="legend-color" style="background:#f39c12;"></span> Medio</div>
                        <div><span class="legend-color" style="background:#2ecc71;"></span> Bajo</div>
                    </div>
                </div>
                <div id="map" aria-label="Mapa predictivo de incidentes"></div>
                <div class="d-flex justify-content-center gap-3 mt-2 d-md-none">
                    <small><span class="legend-color" style="background:#e74c3c;"></span> Alto</small>
                    <small><span class="legend-color" style="background:#f39c12;"></span> Medio</small>
                    <small><span class="legend-color" style="background:#2ecc71;"></span> Bajo</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tipos de incidentes -->
<div class="row">
    <div class="col-12">
        <div class="card card-mobile">
            <div class="card-body">
                <h6 class="mb-3">
                    <i class="bi bi-pie-chart text-primary"></i> Tipos de incidentes
                </h6>
                @if($tiposIncidentes->count() > 0)
                    @foreach($tiposIncidentes as $tipo)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>
                                @php
                                    $iconos = [
                                        'Robo' => '🏠', 'Hurto' => '🚗', 'Asalto' => '👊',
                                        'Vandalismo' => '🎨', 'Violencia' => '👥', 'Emergencia' => '🚑'
                                    ];
                                    $icono = '📍';
                                    foreach($iconos as $key => $ic) {
                                        if(str_contains($tipo->nombre, $key)) {
                                            $icono = $ic;
                                            break;
                                        }
                                    }
                                @endphp
                                {{ $icono }} {{ $tipo->nombre }}
                            </span>
                            <span class="fw-semibold">{{ $tipo->porcentaje }}%</span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar bg-{{ $tipo->porcentaje > 30 ? 'danger' : ($tipo->porcentaje > 15 ? 'warning' : 'info') }}" 
                                 style="width: {{ $tipo->porcentaje }}%"></div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1"></i>
                        <p>No hay datos de incidentes</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Botón para ver todos los reportes -->
<div class="text-center mt-4 mb-5">
    <a href="{{ route('incidentes.index') }}" class="btn btn-outline-secondary btn-mobile">
        <i class="bi bi-list-ul me-2"></i>Ver historial completo
    </a>
</div>
@endsection

@push('scripts')
<!-- Leaflet (mapa gratuito, sin API key) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Inicializar mapa
    let map;
    let markersLayer;
    
    // Centro de Santa Cruz de la Sierra
    const centroSantaCruz = [-17.783333, -63.166667];
    
    function initMap() {
        const mapElement = document.getElementById('map');
        if (!mapElement) return;
        
        map = L.map('map').setView(centroSantaCruz, 13);
        
        // Capa de mapa (OpenStreetMap - gratuito)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; CartoDB',
            subdomains: 'abcd',
            maxZoom: 19,
            minZoom: 11
        }).addTo(map);
        
        markersLayer = L.layerGroup().addTo(map);
        
        // Cargar incidentes iniciales
        @if($barrioActual)
        const barrioId = {{ $barrioActual->id_barrio }};
        cargarIncidentesMapa(barrioId);
        @endif
    }
    
    function cargarIncidentesMapa(barrioId) {
        if (!markersLayer) return;
        markersLayer.clearLayers();
        
        // Obtener datos del barrio vía AJAX
        fetch(`/api/datos-barrio?barrio_id=${barrioId}`)
            .then(response => response.json())
            .then(data => {
                if (data.geojson && data.geojson.features && data.geojson.features.length > 0) {
                    data.geojson.features.forEach(feature => {
                        const coords = feature.geometry.coordinates;
                        const props = feature.properties;
                        
                        // Determinar color según tipo de incidente
                        let color = '#e74c3c';
                        if (props.tipo && props.tipo.includes('Hurto')) color = '#f39c12';
                        if (props.tipo && props.tipo.includes('Vandalismo')) color = '#e67e22';
                        if (props.tipo && props.tipo.includes('Violencia')) color = '#8e44ad';
                        if (props.tipo && props.tipo.includes('Emergencia')) color = '#c0392b';
                        
                        // Crear marcador custom
                        const marker = L.circleMarker([coords[1], coords[0]], {
                            radius: 8,
                            fillColor: color,
                            color: '#fff',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.8
                        }).addTo(markersLayer);
                        
                        // Popup con información
                        marker.bindPopup(`
                            <div style="min-width: 150px;">
                                <strong>${props.tipo || 'Sin tipo'}</strong><br>
                                <small>${props.fecha || 'Fecha no disponible'}</small>
                                ${props.descripcion ? `<br><small>${props.descripcion.substring(0, 80)}</small>` : ''}
                                <br><a href="/incidentes" style="font-size: 12px;">Ver detalles →</a>
                            </div>
                        `);
                    });
                    
                    // Ajustar vista para mostrar todos los marcadores
                    const bounds = [];
                    data.geojson.features.forEach(f => {
                        bounds.push([f.geometry.coordinates[1], f.geometry.coordinates[0]]);
                    });
                    if (bounds.length > 0) {
                        map.fitBounds(bounds);
                    }
                } else {
                    // Si no hay incidentes, centrar en Santa Cruz
                    map.setView(centroSantaCruz, 13);
                }
            })
            .catch(error => console.error('Error al cargar incidentes:', error));
    }
    
    // Cambio de barrio
    const barrioSelector = document.getElementById('barrioSelector');
    if (barrioSelector) {
        barrioSelector.addEventListener('change', function() {
            const barrioId = this.value;
            cargarIncidentesMapa(barrioId);
            // Recargar página para actualizar estadísticas
            window.location.href = `/dashboard?barrio_id=${barrioId}`;
        });
    }
    
    // Botón ver mapa (centrar en ubicación actual)
    const verMapaBtn = document.getElementById('verMapaBtn');
    if (verMapaBtn) {
        verMapaBtn.addEventListener('click', function() {
            if (navigator.geolocation) {
                showToastMessage('Obteniendo ubicación...', 2000);
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        map.setView([position.coords.latitude, position.coords.longitude], 15);
                        showToastMessage('📍 Ubicación actualizada', 2000);
                    },
                    (error) => {
                        let errorMsg = 'No se pudo obtener tu ubicación';
                        if (error.code === 1) errorMsg = 'Permiso denegado. Activa la ubicación';
                        showToastMessage(errorMsg, 3000);
                    }
                );
            } else {
                showToastMessage('Geolocalización no soportada', 3000);
            }
        });
    }
    
    // Gráfico de tendencia
    @php
        $semanasLabels = [];
        $semanasData = [];
        if(isset($tendenciaSemanas) && $tendenciaSemanas->count() > 0) {
            $semanasLabels = $tendenciaSemanas->pluck('fecha')->toArray();
            $semanasData = $tendenciaSemanas->pluck('total')->toArray();
        }
    @endphp
    
    const tendenciaCanvas = document.getElementById('tendenciaChart');
    if (tendenciaCanvas && {!! json_encode(count($semanasData) > 0) !!}) {
        const tendenciaCtx = tendenciaCanvas.getContext('2d');
        const semanasLabels = @json($semanasLabels);
        const semanasData = @json($semanasData);
        
        new Chart(tendenciaCtx, {
            type: 'line',
            data: {
                labels: semanasLabels,
                datasets: [{
                    label: 'Incidentes',
                    data: semanasData,
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#c0392b',
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { 
                        callbacks: { 
                            label: (ctx) => `${ctx.raw} incidentes` 
                        } 
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: '#eee' },
                        title: { display: true, text: 'Número de incidentes' }
                    },
                    x: {
                        title: { display: true, text: 'Semana' }
                    }
                }
            }
        });
    }
    
    // Inicializar mapa cuando el DOM esté listo
    if (document.getElementById('map')) {
        document.addEventListener('DOMContentLoaded', initMap);
    }
    
    // Función para mostrar mensajes toast
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