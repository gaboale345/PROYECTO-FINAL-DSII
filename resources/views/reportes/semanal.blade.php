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
    </style>
</head>
<body>
    <h1>Reporte Semanal</h1>
    <p>Período: {{ $inicio->format('d/m/Y') }} - {{ $fin->format('d/m/Y') }}</p>
    <p>Barrio: {{ $barrio->nombre ?? 'General' }}</p>
    <p>Total incidentes: <strong>{{ $estadisticas['total'] }}</strong></p>
    <table>
        <thead><tr><th>Tipo de delito</th><th>Cantidad</th></tr></thead>
        <tbody>
            @foreach($estadisticas['porTipo'] as $tipo)
            <tr><td>{{ $tipo->nombre }}</td><td>{{ $tipo->total }}</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
