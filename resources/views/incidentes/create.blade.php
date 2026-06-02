@extends('layouts.app')

@section('title', 'Reportar Incidente')

@section('content')
<style>
    .location-btn {
        transition: all 0.3s ease;
    }
    .location-btn:active {
        transform: scale(0.98);
    }
    .security-tip {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px;
        padding: 15px;
        margin-top: 20px;
    }
    .security-tip i {
        font-size: 24px;
        margin-right: 10px;
    }
</style>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <!-- Tarjeta principal -->
        <div class="card card-mobile border-0 shadow-sm">
            <div class="card-body p-4">
                <!-- Encabezado -->
                <div class="text-center mb-4">
                    <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-block">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-1"></i>
                    </div>
                    <h4 class="mt-3 fw-bold">Reportar Incidente</h4>
                    <p class="text-muted small">Completa el formulario en menos de 30 segundos</p>
                    <div class="d-flex justify-content-center gap-3 mt-2">
                        <span class="badge bg-primary"><i class="bi bi-clock"></i> Rápido</span>
                        <span class="badge bg-success"><i class="bi bi-geo-alt"></i> Geolocalización</span>
                        <span class="badge bg-info"><i class="bi bi-shield"></i> Seguro</span>
                    </div>
                </div>
                
                <form action="{{ route('incidentes.store') }}" method="POST" id="incidenteForm" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Tipo de incidente -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-tag me-1 text-danger"></i> Tipo de incidente <span class="text-danger">*</span>
                        </label>
                        <select name="id_tipo_delito" id="tipoDelitoSelect" class="form-select form-mobile @error('id_tipo_delito') is-invalid @enderror">
                            <option value="">🔍 Selecciona un tipo</option>
                            @foreach($tiposDelito as $tipo)
                            <option value="{{ $tipo->id_tipo_delito }}" {{ old('id_tipo_delito') == $tipo->id_tipo_delito ? 'selected' : '' }}>
                                @php
                                    $iconoTipo = '';
                                    if(str_contains($tipo->nombre, 'Robo')) $iconoTipo = '🏠';
                                    elseif(str_contains($tipo->nombre, 'Asalto')) $iconoTipo = '👊';
                                    elseif(str_contains($tipo->nombre, 'Hurto')) $iconoTipo = '🚗';
                                    elseif(str_contains($tipo->nombre, 'Vandalismo')) $iconoTipo = '🎨';
                                    elseif(str_contains($tipo->nombre, 'Violencia')) $iconoTipo = '👥';
                                    elseif(str_contains($tipo->nombre, 'Emergencia')) $iconoTipo = '🚑';
                                    else $iconoTipo = '📍';
                                @endphp
                                {{ $iconoTipo }} {{ $tipo->nombre }}
                            </option>
                            @endforeach
                            <option value="otro" {{ old('id_tipo_delito') === 'otro' ? 'selected' : '' }}>➕ Otro (crear nuevo tipo)</option>
                        </select>

                        <div id="nuevoTipoContainer" class="mt-2" style="{{ old('id_tipo_delito') === 'otro' ? '' : 'display:none;' }}">
                            <input
                                type="text"
                                name="nuevo_tipo_delito"
                                id="nuevoTipoDelitoInput"
                                value="{{ old('nuevo_tipo_delito') }}"
                                class="form-control form-mobile @error('nuevo_tipo_delito') is-invalid @enderror"
                                maxlength="50"
                                placeholder="Ej: Estafa digital"
                            >
                            <small class="text-muted">
                                Si no existe en la lista, escribe el tipo y lo crearemos automaticamente.
                            </small>
                            @error('nuevo_tipo_delito')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        @error('id_tipo_delito')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Descripción -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-chat-text me-1"></i> Descripción
                            <small class="text-muted">(opcional)</small>
                        </label>
                        <textarea name="descripcion" class="form-control form-mobile @error('descripcion') is-invalid @enderror" 
                                  rows="3" placeholder="Describe brevemente lo sucedido... Ej: 'Hombre sospechoso en moto negra'">{{ old('descripcion') }}</textarea>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Máximo 500 caracteres
                        </small>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Ubicación -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-geo-alt-fill me-1 text-success"></i> Ubicación <span class="text-danger">*</span>
                        </label>

                        <p class="small text-muted mb-2">
                            <i class="bi bi-hand-index"></i> Toca el mapa para marcar el punto del incidente, o usa el botón GPS.
                        </p>
                        
                        <div id="miniMapa" style="height: 220px; border-radius: 12px; margin-bottom: 10px; background: #e9ecef; border: 2px solid #dee2e6; z-index: 1;"></div>
                        
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <input type="text" name="latitud" id="latitud" class="form-control form-mobile @error('latitud') is-invalid @enderror" 
                                       placeholder="Latitud" value="{{ old('latitud') }}" inputmode="decimal" required>
                            </div>
                            <div class="col-6">
                                <input type="text" name="longitud" id="longitud" class="form-control form-mobile @error('longitud') is-invalid @enderror" 
                                       placeholder="Longitud" value="{{ old('longitud') }}" inputmode="decimal" required>
                            </div>
                        </div>
                        
                        <button type="button" id="getLocationBtn" class="btn btn-primary btn-mobile w-100 location-btn mb-2">
                            <i class="bi bi-crosshair me-2"></i> Usar mi ubicación GPS
                        </button>

                        <button type="button" id="centroSantaCruzBtn" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-geo"></i> Centrar en Santa Cruz de la Sierra
                        </button>
                        
                        <div id="locationStatus" class="mt-2"></div>
                        
                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i> Si el GPS falla (PC o permiso denegado), marca manualmente en el mapa
                        </small>
                        @error('latitud')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('longitud')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- ========================================== -->
                    <!-- SECCIÓN DE IMÁGENES (AGREGAR ESTO) -->
                    <!-- ========================================== -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-image me-1 text-info"></i> Imágenes del incidente
                            <small class="text-muted">(opcional, máx. 5)</small>
                        </label>
                        
                        <div class="border rounded-3 p-3" style="background: #f8f9fa;">
                            <!-- Área de preview -->
                            <div id="previewContainer" class="row g-2 mb-3" style="min-height: 100px;">
                                <div class="col-12 text-center text-muted" id="emptyPreview">
                                    <i class="bi bi-camera fs-1"></i>
                                    <p class="small mb-0">No hay imágenes seleccionadas</p>
                                </div>
                            </div>
                            
                            <!-- Botón seleccionar -->
                            <div class="d-flex justify-content-center">
                                <label class="btn btn-outline-secondary btn-mobile" style="cursor: pointer;">
                                    <i class="bi bi-cloud-upload"></i> Seleccionar imágenes
                                    <input type="file" name="imagenes[]" id="imagenesInput" 
                                           class="d-none" accept="image/jpeg,image/png,image/jpg,image/gif" multiple>
                                </label>
                            </div>
                            
                            <small class="text-muted d-block text-center mt-2">
                                <i class="bi bi-info-circle"></i> Formatos: JPG, PNG, GIF. Máx. 5MB por imagen
                            </small>
                            
                            <div id="errorImagenes" class="text-danger small mt-2"></div>
                        </div>
                    </div>
                    
                    <!-- Reporte por voz -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-mic-fill me-1 text-primary"></i> Reportar por voz
                            <small class="text-muted">(opcional)</small>
                        </label>
                        <button type="button" id="voiceBtn" class="btn btn-outline-primary btn-mobile w-100 btn-lg" aria-label="Dictar descripción del incidente">
                            <i class="bi bi-mic"></i> Mantén pulsado para dictar
                        </button>
                        <input type="hidden" name="es_por_voz" id="esPorVoz" value="0">
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle"></i> Ideal para adultos mayores: describe el incidente hablando
                        </small>
                    </div>

                    <!-- Opciones adicionales -->
                    <div class="mb-4 p-3 bg-light rounded-3">
                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" name="es_anonimo" class="form-check-input" id="anonimoSwitch" value="1" {{ old('es_anonimo') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="anonimoSwitch">
                                <i class="bi bi-incognito me-1"></i> Reportar de forma anónima
                            </label>
                        </div>
                        <small class="text-muted ms-4">
                            Tu identidad no será visible para otros usuarios, solo para las autoridades
                        </small>
                    </div>
                    
                    <!-- Botón enviar -->
                    <button type="submit" class="btn btn-danger btn-mobile w-100 py-3 fw-bold" id="submitBtn">
                        <i class="bi bi-send-fill me-2"></i> REPORTAR INCIDENTE
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Consejos de seguridad -->
        <div class="security-tip">
            <div class="d-flex align-items-start">
                <i class="bi bi-shield-check"></i>
                <div>
                    <strong class="d-block mb-1">Consejos de seguridad</strong>
                    <ul class="small mb-0 ps-3" style="color: rgba(255,255,255,0.9);">
                        <li>Si estás en peligro, llama primero al <strong>911</strong> o <strong>110</strong></li>
                        <li>Toma fotos del lugar si es seguro hacerlo</li>
                        <li>Comparte este reporte con vecinos cercanos</li>
                        <li>Mantén activas las notificaciones para recibir alertas</li>
                        <li>No te expongas, tu seguridad es lo más importante</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Ver historial -->
        <div class="text-center mt-3">
            <a href="{{ route('incidentes.index') }}" class="text-decoration-none">
                <i class="bi bi-clock-history"></i> Ver historial de incidentes
            </a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #miniMapa { touch-action: manipulation; position: relative; z-index: 1; }
    #miniMapa .leaflet-pane { z-index: 400; }
    .leaflet-container { font-family: inherit; }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const CENTRO_SANTA_CRUZ = [-17.783333, -63.166667];
    let miniMap = null;
    let marker = null;

    const latInput = document.getElementById('latitud');
    const lngInput = document.getElementById('longitud');
    const getLocationBtn = document.getElementById('getLocationBtn');
    const centroSantaCruzBtn = document.getElementById('centroSantaCruzBtn');
    const locationStatus = document.getElementById('locationStatus');
    const submitBtn = document.getElementById('submitBtn');
    const tipoDelitoSelect = document.getElementById('tipoDelitoSelect');
    const nuevoTipoContainer = document.getElementById('nuevoTipoContainer');
    const nuevoTipoDelitoInput = document.getElementById('nuevoTipoDelitoInput');

    function actualizarCoordenadas(lat, lng, mensaje) {
        latInput.value = Number(lat).toFixed(7);
        lngInput.value = Number(lng).toFixed(7);

        if (marker) {
            marker.setLatLng([lat, lng]);
        } else if (miniMap) {
            marker = L.marker([lat, lng], { draggable: true }).addTo(miniMap);
            marker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                actualizarCoordenadas(pos.lat, pos.lng, 'Ubicación ajustada en el mapa');
            });
        }

        if (miniMap) {
            miniMap.setView([lat, lng], miniMap.getZoom() < 14 ? 15 : miniMap.getZoom());
        }

        if (mensaje) {
            locationStatus.innerHTML = `<div class="alert alert-success py-2 mb-0"><i class="bi bi-check-circle"></i> ${mensaje}</div>`;
        }
    }

    function initMapa() {
        const latOld = parseFloat(latInput.value);
        const lngOld = parseFloat(lngInput.value);
        const centro = (!isNaN(latOld) && !isNaN(lngOld)) ? [latOld, lngOld] : CENTRO_SANTA_CRUZ;
        const zoom = (!isNaN(latOld) && !isNaN(lngOld)) ? 15 : 12;

        miniMap = L.map('miniMapa', { tap: true }).setView(centro, zoom);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CartoDB',
            maxZoom: 19
        }).addTo(miniMap);

        miniMap.on('click', function (e) {
            actualizarCoordenadas(e.latlng.lat, e.latlng.lng, 'Ubicación marcada en el mapa');
            showToastMessage('📍 Punto marcado correctamente', 2000);
        });

        if (!isNaN(latOld) && !isNaN(lngOld)) {
            actualizarCoordenadas(latOld, lngOld, null);
        }

        setTimeout(function () {
            miniMap.invalidateSize();
        }, 300);
    }

    function getLocation() {
        getLocationBtn.disabled = true;
        getLocationBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Obteniendo GPS...';
        locationStatus.innerHTML = '<div class="alert alert-info py-2 mb-0"><i class="bi bi-hourglass-split"></i> Solicitando permiso de ubicación...</div>';

        if (!navigator.geolocation) {
            locationStatus.innerHTML = '<div class="alert alert-warning py-2 mb-0"><i class="bi bi-exclamation-triangle"></i> GPS no disponible. Marca el punto tocando el mapa.</div>';
            getLocationBtn.disabled = false;
            getLocationBtn.innerHTML = '<i class="bi bi-crosshair me-2"></i> Usar mi ubicación GPS';
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function (position) {
                actualizarCoordenadas(
                    position.coords.latitude,
                    position.coords.longitude,
                    `GPS: precisión ~${Math.round(position.coords.accuracy)} m`
                );
                showToastMessage('📍 Ubicación GPS obtenida', 2000);
                getLocationBtn.disabled = false;
                getLocationBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i> GPS actualizado';
            },
            function (error) {
                const mensajes = {
                    1: 'Permiso denegado. Marca manualmente en el mapa o activa la ubicación en el navegador.',
                    2: 'Señal no disponible. Marca el punto tocando el mapa.',
                    3: 'Tiempo agotado. Marca el punto tocando el mapa o reintenta GPS.'
                };
                const errorMsg = mensajes[error.code] || 'No se pudo usar GPS. Marca el punto en el mapa.';
                locationStatus.innerHTML = `<div class="alert alert-warning py-2 mb-0"><i class="bi bi-exclamation-triangle"></i> ${errorMsg}</div>`;
                showToastMessage(errorMsg, 5000);
                getLocationBtn.disabled = false;
                getLocationBtn.innerHTML = '<i class="bi bi-crosshair me-2"></i> Reintentar GPS';
            },
            { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 }
        );
    }

    initMapa();
    getLocationBtn.addEventListener('click', getLocation);

    if (centroSantaCruzBtn) {
        centroSantaCruzBtn.addEventListener('click', function () {
            miniMap.setView(CENTRO_SANTA_CRUZ, 12);
            showToastMessage('Mapa centrado en Santa Cruz', 2000);
        });
    }

    latInput.addEventListener('change', function () {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);
        if (!isNaN(lat) && !isNaN(lng)) {
            actualizarCoordenadas(lat, lng, 'Coordenadas actualizadas');
        }
    });

    lngInput.addEventListener('change', function () {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);
        if (!isNaN(lat) && !isNaN(lng)) {
            actualizarCoordenadas(lat, lng, 'Coordenadas actualizadas');
        }
    });

    // Intentar GPS al cargar (si falla, el mapa manual sigue disponible)
    setTimeout(getLocation, 800);

    if (tipoDelitoSelect) {
        tipoDelitoSelect.addEventListener('change', function () {
            const seleccionoOtro = this.value === 'otro';
            if (nuevoTipoContainer) {
                nuevoTipoContainer.style.display = seleccionoOtro ? 'block' : 'none';
            }

            if (nuevoTipoDelitoInput) {
                if (seleccionoOtro) {
                    nuevoTipoDelitoInput.setAttribute('required', 'required');
                } else {
                    nuevoTipoDelitoInput.removeAttribute('required');
                    nuevoTipoDelitoInput.value = '';
                }
            }
        });
    }
    
    // ============================================
    // MANEJO DE IMÁGENES
    // ============================================
    const imagenesInput = document.getElementById('imagenesInput');
    const previewContainer = document.getElementById('previewContainer');
    let imagenesSeleccionadas = [];
    
    if (imagenesInput) {
        imagenesInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            const errorDiv = document.getElementById('errorImagenes');
            
            if (files.length > 5) {
                errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Máximo 5 imágenes permitidas';
                imagenesInput.value = '';
                return;
            }
            
            let validFiles = [];
            for (let file of files) {
                if (file.size > 5 * 1024 * 1024) {
                    errorDiv.innerHTML = `<i class="bi bi-exclamation-triangle"></i> "${file.name}" excede 5MB`;
                    imagenesInput.value = '';
                    return;
                }
                if (!file.type.startsWith('image/')) {
                    errorDiv.innerHTML = `<i class="bi bi-exclamation-triangle"></i> "${file.name}" no es una imagen`;
                    imagenesInput.value = '';
                    return;
                }
                validFiles.push(file);
            }
            
            errorDiv.innerHTML = '';
            imagenesSeleccionadas = validFiles;
            previewContainer.innerHTML = '';
            
            if (validFiles.length === 0) {
                previewContainer.innerHTML = `<div class="col-12 text-center text-muted"><i class="bi bi-camera fs-1"></i><p class="small mb-0">No hay imágenes seleccionadas</p></div>`;
                return;
            }
            
            validFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-4 col-md-3';
                    col.innerHTML = `
                        <div class="position-relative">
                            <img src="${e.target.result}" class="img-fluid rounded" style="height: 100px; width: 100%; object-fit: cover;">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle" 
                                    style="width: 24px; height: 24px; padding: 0; font-size: 12px;" 
                                    onclick="eliminarImagenSeleccionada(${index})">
                                <i class="bi bi-x"></i>
                            </button>
                            <small class="d-block text-center text-muted mt-1">${file.name.substring(0, 12)}${file.name.length > 12 ? '...' : ''}</small>
                        </div>
                    `;
                    previewContainer.appendChild(col);
                };
                reader.readAsDataURL(file);
            });
        });
    }
    
    function eliminarImagenSeleccionada(index) {
        const newFiles = imagenesSeleccionadas.filter((_, i) => i !== index);
        imagenesSeleccionadas = newFiles;
        const dataTransfer = new DataTransfer();
        newFiles.forEach(file => dataTransfer.items.add(file));
        imagenesInput.files = dataTransfer.files;
        imagenesInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
    
    // Reporte por voz (Web Speech API)
    const voiceBtn = document.getElementById('voiceBtn');
    const descField = document.querySelector('textarea[name="descripcion"]');
    const esPorVoz = document.getElementById('esPorVoz');
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (voiceBtn && SpeechRecognition) {
        const recognition = new SpeechRecognition();
        recognition.lang = 'es-BO';
        recognition.interimResults = false;

        voiceBtn.addEventListener('click', () => {
            voiceBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Escuchando...';
            recognition.start();
        });

        recognition.onresult = (event) => {
            const texto = event.results[0][0].transcript;
            descField.value = (descField.value ? descField.value + ' ' : '') + texto;
            esPorVoz.value = '1';
            voiceBtn.innerHTML = '<i class="bi bi-check-circle"></i> Voz capturada';
            showToastMessage('Descripción dictada correctamente', 2000);
        };

        recognition.onerror = () => {
            voiceBtn.innerHTML = '<i class="bi bi-mic"></i> Mantén pulsado para dictar';
            showToastMessage('No se pudo usar el micrófono', 3000);
        };

        recognition.onend = () => {
            if (!voiceBtn.innerHTML.includes('check-circle')) {
                voiceBtn.innerHTML = '<i class="bi bi-mic"></i> Mantén pulsado para dictar';
            }
        };
    } else if (voiceBtn) {
        voiceBtn.disabled = true;
        voiceBtn.title = 'Tu navegador no soporta dictado por voz';
    }

    // Validación antes de enviar
    document.getElementById('incidenteForm').addEventListener('submit', function(e) {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);

        if (isNaN(lat) || isNaN(lng) || latInput.value.trim() === '' || lngInput.value.trim() === '') {
            e.preventDefault();
            showToastMessage('⚠️ Marca la ubicación en el mapa o usa el botón GPS', 4000);
            locationStatus.innerHTML = '<div class="alert alert-danger py-2 mb-0"><i class="bi bi-geo-alt"></i> Debes marcar un punto en el mapa antes de enviar.</div>';
            return false;
        }
        
        const tipoSelect = document.querySelector('select[name="id_tipo_delito"]');
        if (!tipoSelect.value) {
            e.preventDefault();
            showToastMessage('⚠️ Selecciona el tipo de incidente', 3000);
            tipoSelect.focus();
            return false;
        }

        if (tipoSelect.value === 'otro' && (!nuevoTipoDelitoInput || !nuevoTipoDelitoInput.value.trim())) {
            e.preventDefault();
            showToastMessage('⚠️ Escribe el nuevo tipo de incidente', 3000);
            if (nuevoTipoDelitoInput) nuevoTipoDelitoInput.focus();
            return false;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enviando reporte...';
    });
    
    // Toast message
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
        setTimeout(function () { toast.style.display = 'none'; }, duration);
    }
}); // DOMContentLoaded
</script>
@endpush