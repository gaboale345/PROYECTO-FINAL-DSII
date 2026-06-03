@php
    $inc = $incidente ?? null;
@endphp

<div class="admin-form-section">
    <h6><i class="bi bi-person-badge"></i> Reporte y clasificación</h6>
    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <label class="form-label fw-semibold">Reportante <span class="text-danger">*</span></label>
            <select name="id_usuario_reportante" class="form-select form-select-lg" required>
                <option value="">Seleccionar usuario</option>
                @foreach($usuarios as $u)
                <option value="{{ $u->id_usuario }}"
                    {{ (int) old('id_usuario_reportante', $inc?->id_usuario_reportante) === (int) $u->id_usuario ? 'selected' : '' }}>
                    {{ $u->nombre_completo }}@if($u->barrio) — {{ $u->barrio->nombre }}@endif
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-lg-6">
            <label class="form-label fw-semibold">Tipo de delito <span class="text-danger">*</span></label>
            <select name="id_tipo_delito" class="form-select form-select-lg" required>
                @foreach($tiposDelito as $tipo)
                <option value="{{ $tipo->id_tipo_delito }}"
                    {{ (int) old('id_tipo_delito', $inc?->id_tipo_delito) === (int) $tipo->id_tipo_delito ? 'selected' : '' }}>
                    {{ $tipo->nombre }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3" placeholder="Detalle del incidente…">{{ old('descripcion', $inc?->descripcion) }}</textarea>
        </div>
    </div>
</div>

<div class="admin-form-section">
    <h6><i class="bi bi-geo-alt"></i> Ubicación y fecha</h6>
    <div class="row g-3">
        <div class="col-12 col-sm-6">
            <label class="form-label fw-semibold">Latitud <span class="text-danger">*</span></label>
            <input type="number" step="any" name="latitud" class="form-control" required
                   value="{{ old('latitud', $inc?->latitud ?? -17.7833) }}">
        </div>
        <div class="col-12 col-sm-6">
            <label class="form-label fw-semibold">Longitud <span class="text-danger">*</span></label>
            <input type="number" step="any" name="longitud" class="form-control" required
                   value="{{ old('longitud', $inc?->longitud ?? -63.1821) }}">
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Fecha y hora <span class="text-danger">*</span></label>
            <input type="datetime-local" name="fecha_hora" class="form-control" required
                   value="{{ old('fecha_hora', $inc?->fecha_hora ? $inc->fecha_hora->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
        </div>
    </div>
</div>

<div class="admin-form-section">
    <h6><i class="bi bi-sliders"></i> Estado y validación</h6>
    <div class="row g-3">
        <div class="col-12 col-sm-6">
            <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
            <select name="estado" class="form-select" required>
                @foreach($estados as $valor => $etiqueta)
                <option value="{{ $valor }}" {{ old('estado', $inc?->estado ?? 'pendiente') === $valor ? 'selected' : '' }}>
                    {{ $etiqueta }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6">
            <label class="form-label fw-semibold">Fusionado con (ID principal)</label>
            <input type="number" name="id_incidente_principal" class="form-control" min="1"
                   value="{{ old('id_incidente_principal', $inc?->id_incidente_principal) }}" placeholder="Opcional">
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold d-block mb-2">Marcadores</label>
            <div class="d-flex flex-wrap gap-2">
                <label class="admin-check-pill">
                    <input type="checkbox" name="validado" value="1" {{ old('validado', $inc?->validado) ? 'checked' : '' }}>
                    <span><i class="bi bi-check-circle text-success"></i> Validado</span>
                </label>
                <label class="admin-check-pill">
                    <input type="checkbox" name="es_falso_reporte" value="1" {{ old('es_falso_reporte', $inc?->es_falso_reporte) ? 'checked' : '' }}>
                    <span><i class="bi bi-x-circle text-danger"></i> Falso reporte</span>
                </label>
                <label class="admin-check-pill">
                    <input type="checkbox" name="es_anonimo" value="1" {{ old('es_anonimo', $inc?->es_anonimo) ? 'checked' : '' }}>
                    <span><i class="bi bi-incognito"></i> Anónimo</span>
                </label>
                <label class="admin-check-pill">
                    <input type="checkbox" name="es_por_voz" value="1" {{ old('es_por_voz', $inc?->es_por_voz) ? 'checked' : '' }}>
                    <span><i class="bi bi-mic"></i> Por voz</span>
                </label>
                <label class="admin-check-pill">
                    <input type="checkbox" name="es_whatsapp" value="1" {{ old('es_whatsapp', $inc?->es_whatsapp) ? 'checked' : '' }}>
                    <span><i class="bi bi-whatsapp text-success"></i> WhatsApp</span>
                </label>
            </div>
        </div>
    </div>
</div>
