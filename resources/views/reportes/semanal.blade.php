<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Semanal - Santa Cruz Segura</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h1 { color: #c0392b; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #2c3e50; color: #fff; }
        .filtro-form { background: #f8f9fa; padding: 16px; border-radius: 8px; margin-bottom: 24px; }
        .filtro-form label { display: block; font-weight: 600; margin-bottom: 4px; }
        .filtro-form input, .filtro-form select { padding: 8px; margin-bottom: 12px; max-width: 220px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    @php
        $queryPdf = array_filter([
            'formato' => 'pdf',
            'fecha_desde' => $fechaDesde->format('Y-m-d'),
            'fecha_hasta' => $fechaHasta->format('Y-m-d'),
            'barrio_id' => $barrioId ?? null,
        ], fn ($v) => $v !== null && $v !== '');
        $urlPdf = '?' . http_build_query($queryPdf);
    @endphp

    @if(empty($pdf))
    @if(session('error'))
    <p class="no-print" style="color:#c0392b;font-weight:600;">{{ session('error') }}</p>
    @endif
    <form class="filtro-form no-print" method="get" action="{{ url('/reportes/semanal') }}">
        <h3 style="margin-top:0;">Reporte semanal</h3>
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
                    <option value="">Todos</option>
                    @foreach($barrios as $b)
                    <option value="{{ $b->id_barrio }}" {{ ($barrioId ?? null) == $b->id_barrio ? 'selected' : '' }}>{{ $b->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <p style="margin:12px 0 0;">
            <button type="submit">Ver reporte</button>
            <a href="{{ $urlPdf }}" target="_blank" rel="noopener">Descargar PDF</a>
            <button type="button" onclick="window.print()">Imprimir</button>
        </p>
    </form>
    @endif

    <h1>Reporte Semanal</h1>
    <p>Período: <strong>{{ $fechaDesde->format('d/m/Y') }}</strong> - <strong>{{ $fechaHasta->format('d/m/Y') }}</strong></p>
    <p>Barrio: {{ $barrio->nombre ?? 'General' }}</p>
    <p>Total incidentes validados: <strong>{{ $estadisticas['total'] }}</strong></p>
    <table>
        <thead><tr><th>Tipo de delito</th><th>Cantidad</th></tr></thead>
        <tbody>
            @forelse($estadisticas['porTipo'] as $tipo)
            <tr><td>{{ $tipo->nombre }}</td><td>{{ $tipo->total }}</td></tr>
            @empty
            <tr><td colspan="2">Sin datos en el período</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
