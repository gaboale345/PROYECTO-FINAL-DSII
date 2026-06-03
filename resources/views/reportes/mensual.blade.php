<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte - Santa Cruz Segura</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; color: #222; }
        h1 { color: #c0392b; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #2c3e50; color: #fff; }
        .meta { color: #666; margin-bottom: 30px; }
        .filtro-form { background: #f8f9fa; padding: 16px; border-radius: 8px; margin-bottom: 24px; }
        .filtro-form label { display: block; font-weight: 600; margin-bottom: 4px; }
        .filtro-form input, .filtro-form select { padding: 8px; margin-bottom: 12px; width: 100%; max-width: 220px; }
        .filtro-form .acciones { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-top: 8px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    @php
        $queryBase = array_filter([
            'fecha_desde' => $fechaDesde->format('Y-m-d'),
            'fecha_hasta' => $fechaHasta->format('Y-m-d'),
            'barrio_id' => $barrioId ?? null,
        ], fn ($v) => $v !== null && $v !== '');
        $queryPdf = array_merge($queryBase, ['formato' => 'pdf']);
        $urlPdf = '?' . http_build_query($queryPdf);
    @endphp

    @if(empty($pdf))
    @if(session('error'))
    <p class="no-print" style="color:#c0392b;font-weight:600;">{{ session('error') }}</p>
    @endif
    <form class="filtro-form no-print" method="get" action="{{ url('/reportes/mensual') }}">
        <h3 style="margin-top:0;">Generar reporte</h3>
        <div style="display:flex; flex-wrap:wrap; gap:16px;">
            <div>
                <label for="fecha_desde">Desde</label>
                <input type="date" id="fecha_desde" name="fecha_desde" value="{{ $fechaDesde->format('Y-m-d') }}" required>
            </div>
            <div>
                <label for="fecha_hasta">Hasta</label>
                <input type="date" id="fecha_hasta" name="fecha_hasta" value="{{ $fechaHasta->format('Y-m-d') }}" required>
            </div>
            <div>
                <label for="barrio_id">Barrio</label>
                <select id="barrio_id" name="barrio_id">
                    <option value="">Todos los barrios</option>
                    @foreach($barrios as $b)
                    <option value="{{ $b->id_barrio }}" {{ ($barrioId ?? null) == $b->id_barrio ? 'selected' : '' }}>
                        {{ $b->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="acciones">
            <button type="submit">Ver reporte</button>
            <a href="{{ $urlPdf }}" target="_blank" rel="noopener">Descargar PDF</a>
            <button type="button" onclick="window.print()">Imprimir</button>
        </div>
    </form>
    @endif

    <h1>Santa Cruz Segura Predictiva</h1>
    <h2>Reporte de incidentes</h2>
    <p class="meta">
        Período: <strong>{{ $fechaDesde->format('d/m/Y') }}</strong> al <strong>{{ $fechaHasta->format('d/m/Y') }}</strong><br>
        Barrio: <strong>{{ $barrio->nombre ?? 'Todos los barrios' }}</strong><br>
        Generado: {{ now()->format('d/m/Y H:i') }}
    </p>

    <h3>Resumen</h3>
    <ul>
        <li>Incidentes validados: <strong>{{ $estadisticas['total'] }}</strong></li>
        <li>Falsos reportes: <strong>{{ $estadisticas['falsos'] }}</strong></li>
    </ul>

    <h3>Por tipo de delito</h3>
    <table>
        <thead><tr><th>Tipo</th><th>Total</th></tr></thead>
        <tbody>
            @forelse($estadisticas['porTipo'] as $tipo)
            <tr><td>{{ $tipo->nombre }}</td><td>{{ $tipo->total }}</td></tr>
            @empty
            <tr><td colspan="2">Sin datos en el período seleccionado</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>Tendencia diaria</h3>
    <table>
        <thead><tr><th>Fecha</th><th>Incidentes</th></tr></thead>
        <tbody>
            @forelse($estadisticas['porDia'] as $dia)
            <tr>
                <td>{{ $dia->dia ? \Carbon\Carbon::parse($dia->dia)->format('d/m/Y') : '—' }}</td>
                <td>{{ $dia->total }}</td>
            </tr>
            @empty
            <tr><td colspan="2">Sin datos en el período seleccionado</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="meta"><small>Documento para solicitud de iluminación/cámaras a la Alcaldía · Ley 164 de Protección de Datos Personales (Bolivia)</small></p>
</body>
</html>
