<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Mensual - Santa Cruz Segura</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; color: #222; }
        h1 { color: #c0392b; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #2c3e50; color: #fff; }
        .meta { color: #666; margin-bottom: 30px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    @if(empty($pdf))
    <p class="no-print"><a href="?formato=pdf">Descargar PDF</a> · <button onclick="window.print()">Imprimir</button></p>
    @endif

    <h1>Santa Cruz Segura Predictiva</h1>
    <h2>Reporte Mensual {{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}/{{ $anio }}</h2>
    <p class="meta">
        Barrio: <strong>{{ $barrio->nombre ?? 'Todos los barrios piloto' }}</strong><br>
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
            @foreach($estadisticas['porTipo'] as $tipo)
            <tr><td>{{ $tipo->nombre }}</td><td>{{ $tipo->total }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h3>Tendencia diaria</h3>
    <table>
        <thead><tr><th>Día</th><th>Incidentes</th></tr></thead>
        <tbody>
            @foreach($estadisticas['porDia'] as $dia)
            <tr><td>{{ $dia->dia }}</td><td>{{ $dia->total }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <p class="meta"><small>Documento para solicitud de iluminación/cámaras a la Alcaldía · Ley 164 de Protección de Datos Personales (Bolivia)</small></p>
</body>
</html>
