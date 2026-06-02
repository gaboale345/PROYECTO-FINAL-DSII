<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Incidente;
use App\Models\Barrio;
use App\Models\TipoDelito;
use App\Models\Usuario;
use App\Services\PredictionService;

class DashboardController extends Controller
{
    public function __construct(
        private PredictionService $predictions
    ) {}

    /**
     * Muestra el dashboard principal con mapa predictivo
     */
    public function index(Request $request)
    {
        $barrios = Barrio::where('activo', true)->get();

        $barrioActual = $request->filled('barrio_id')
            ? $barrios->firstWhere('id_barrio', (int) $request->barrio_id)
            : null;
        $barrioActual ??= $barrios->first();

        if (!$barrioActual) {
            return view('dashboard', $this->dashboardVacio($barrios));
        }

        $barrioId = $barrioActual->id_barrio;

        // 1. Datos del barrio seleccionado
        $totalIncidentes30d = Incidente::whereHas('reportante', function($q) use ($barrioId) {
                $q->where('id_barrio', $barrioId);
            })
            ->where('fecha_hora', '>=', now()->subDays(30))
            ->count();
        
        // Contar zonas críticas (barrios con alta incidencia)
        $zonasCriticas = Barrio::whereHas('usuarios', function($q) {
                $q->whereHas('incidentesReportados', function($sub) {
                    $sub->where('fecha_hora', '>=', now()->subDays(30))
                        ->where('validado', true);
                });
            })
            ->withCount(['usuarios as incidentes_count' => function($q) {
                $q->whereHas('incidentesReportados', function($sub) {
                    $sub->where('fecha_hora', '>=', now()->subDays(30))
                        ->where('validado', true);
                });
            }])
            ->having('incidentes_count', '>', 5)
            ->count();
        
        // Participación vecinal (usuarios activos que han reportado)
        $participacionVecinal = Usuario::whereHas('incidentesReportados', function($q) {
                $q->where('fecha_hora', '>=', now()->subDays(30));
            })
            ->count();
        
        // Calcular tendencia (comparación con período anterior)
        $periodoActual = Incidente::whereHas('reportante', function($q) use ($barrioId) {
                $q->where('id_barrio', $barrioId);
            })
            ->whereBetween('fecha_hora', [now()->subDays(30), now()])
            ->count();
        
        $periodoAnterior = Incidente::whereHas('reportante', function($q) use ($barrioId) {
                $q->where('id_barrio', $barrioId);
            })
            ->whereBetween('fecha_hora', [now()->subDays(60), now()->subDays(30)])
            ->count();
        
        $tendencia = $periodoAnterior > 0 
            ? round((($periodoActual - $periodoAnterior) / $periodoAnterior) * 100)
            : 0;
        
        // Determinar nivel de riesgo basado en incidentes
        $nivelRiesgo = $this->calcularNivelRiesgo($totalIncidentes30d);
        
        // 2. Datos para el mapa predictivo (incidentes del barrio)
        $incidentesMapa = Incidente::with(['tipoDelito', 'reportante'])
            ->whereHas('reportante', function($q) use ($barrioId) {
                $q->where('id_barrio', $barrioId);
            })
            ->where('validado', true)
            ->where('es_falso_reporte', false)
            ->where('fecha_hora', '>=', now()->subDays(30))
            ->get();
        
        // 3. Tendencia de incidentes (últimas 4 semanas)
        $tendenciaSemanas = [];
        for ($i = 3; $i >= 0; $i--) {
            $semanaInicio = now()->subDays(($i + 1) * 7);
            $semanaFin = now()->subDays($i * 7);
            
            $total = Incidente::whereHas('reportante', function($q) use ($barrioId) {
                    $q->where('id_barrio', $barrioId);
                })
                ->whereBetween('fecha_hora', [$semanaInicio, $semanaFin])
                ->count();
            
            $tendenciaSemanas[] = [
                'fecha' => $semanaFin->format('M d'),
                'total' => $total
            ];
        }
        
        $tendenciaSemanas = collect($tendenciaSemanas);

        
        // 4. Tipos de incidentes (distribución porcentual)
        $tiposIncidentes = TipoDelito::select('tipos_delito.nombre', DB::raw('COUNT(incidentes.id_incidente) as total'))
            ->leftJoin('incidentes', function($join) use ($barrioId) {
                $join->on('tipos_delito.id_tipo_delito', '=', 'incidentes.id_tipo_delito')
                    ->join('usuarios', 'incidentes.id_usuario_reportante', '=', 'usuarios.id_usuario')
                    ->where('usuarios.id_barrio', $barrioId)
                    ->where('incidentes.fecha_hora', '>=', now()->subDays(30));
            })
            ->groupBy('tipos_delito.id_tipo_delito', 'tipos_delito.nombre')
            ->get();
        
        $totalIncidentesTipo = $tiposIncidentes->sum('total');
        foreach ($tiposIncidentes as $tipo) {
            $tipo->porcentaje = $totalIncidentesTipo > 0 
                ? round(($tipo->total / $totalIncidentesTipo) * 100) 
                : 0;
        }
        
        // 5. Datos para el mapa de calor (geojson)
        $geojson = $this->generarGeoJson($incidentesMapa);
        
        return view('dashboard', compact(
            'barrioActual',
            'totalIncidentes30d',
            'zonasCriticas',
            'participacionVecinal',
            'tendencia',
            'nivelRiesgo',
            'incidentesMapa',
            'tendenciaSemanas',
            'tiposIncidentes',
            'geojson',
            'barrios'
        ));
    }
    
    /**
     * Datos por defecto cuando no hay barrios configurados
     */
    private function dashboardVacio($barrios)
    {
        return [
            'barrioActual' => null,
            'totalIncidentes30d' => 0,
            'zonasCriticas' => 0,
            'participacionVecinal' => 0,
            'tendencia' => 0,
            'nivelRiesgo' => $this->calcularNivelRiesgo(0),
            'incidentesMapa' => collect(),
            'tendenciaSemanas' => collect(),
            'tiposIncidentes' => collect(),
            'geojson' => ['type' => 'FeatureCollection', 'features' => []],
            'barrios' => $barrios,
        ];
    }

    /**
     * Calcular nivel de riesgo basado en incidentes
     */
    private function calcularNivelRiesgo($totalIncidentes)
    {
        if ($totalIncidentes >= 30) return ['texto' => 'CRÍTICO', 'clase' => 'danger', 'icono' => 'bi-emoji-dizzy'];
        if ($totalIncidentes >= 15) return ['texto' => 'ALTO', 'clase' => 'danger', 'icono' => 'bi-exclamation-triangle'];
        if ($totalIncidentes >= 8) return ['texto' => 'MEDIO', 'clase' => 'warning', 'icono' => 'bi-shield-exclamation'];
        if ($totalIncidentes >= 3) return ['texto' => 'BAJO', 'clase' => 'info', 'icono' => 'bi-shield-check'];
        return ['texto' => 'SEGURO', 'clase' => 'success', 'icono' => 'bi-emoji-smile'];
    }
    
    /**
     * Generar GeoJSON para el mapa
     */
    private function generarGeoJson($incidentes)
    {
        $features = [];
        foreach ($incidentes as $incidente) {
            $color = $this->getColorByTipo($incidente->tipoDelito->nombre ?? '');
            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float) $incidente->longitud, (float) $incidente->latitud]
                ],
                'properties' => [
                    'id' => $incidente->id_incidente,
                    'tipo' => $incidente->tipoDelito->nombre ?? 'Sin clasificar',
                    'descripcion' => substr($incidente->descripcion ?? 'Sin descripción', 0, 100),
                    'fecha' => $incidente->fecha_hora ? $incidente->fecha_hora->format('d/m/Y H:i') : 'N/A',
                    'color' => $color,
                    'icono' => $this->getIconByTipo($incidente->tipoDelito->nombre ?? '')
                ]
            ];
        }
        
        return [
            'type' => 'FeatureCollection',
            'features' => $features
        ];
    }
    
    private function getColorByTipo($tipo)
    {
        $colores = [
            'Robo' => '#e74c3c',
            'Asalto' => '#c0392b',
            'Hurto' => '#f39c12',
            'Vandalismo' => '#e67e22',
            'Violencia' => '#8e44ad',
            'Emergencia' => '#e74c3c'
        ];
        
        foreach ($colores as $key => $color) {
            if (str_contains($tipo, $key)) return $color;
        }
        return '#3498db';
    }
    
    private function getIconByTipo($tipo)
    {
        $iconos = [
            'Robo' => 'bi-shield-shaded',
            'Asalto' => 'bi-person-bounding-box',
            'Hurto' => 'bi-car-front',
            'Vandalismo' => 'bi-brush',
            'Violencia' => 'bi-people',
            'Emergencia' => 'bi-heart-pulse'
        ];
        
        foreach ($iconos as $key => $icono) {
            if (str_contains($tipo, $key)) return $icono;
        }
        return 'bi-pin-map-fill';
    }
    
    /**
     * API para datos del mapa (cambio de barrio)
     */
    public function getDatosBarrio(Request $request)
    {
        $barrioId = $request->get('barrio_id');
        $barrio = $barrioId
            ? Barrio::where('activo', true)->find($barrioId)
            : Barrio::where('activo', true)->first();
        
        if (!$barrio) {
            return response()->json(['error' => 'Barrio no encontrado'], 404);
        }
        
        // Incidentes del barrio
        $incidentes = Incidente::with(['tipoDelito'])
            ->whereHas('reportante', function($q) use ($barrio) {
                $q->where('id_barrio', $barrio->id_barrio);
            })
            ->where('validado', true)
            ->where('es_falso_reporte', false)
            ->where('fecha_hora', '>=', now()->subDays(30))
            ->get();
        
        // Generar GeoJSON
        $features = [];
        foreach ($incidentes as $incidente) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float) $incidente->longitud, (float) $incidente->latitud]
                ],
                'properties' => [
                    'tipo' => $incidente->tipoDelito->nombre ?? 'Sin clasificar',
                    'fecha' => $incidente->fecha_hora ? $incidente->fecha_hora->format('d/m/Y H:i') : 'N/A'
                ]
            ];
        }
        
        return response()->json([
            'barrio' => $barrio,
            'total_incidentes' => $incidentes->count(),
            'geojson' => [
                'type' => 'FeatureCollection',
                'features' => $features
            ]
        ]);
    }

    /**
     * Mapa predictivo con zonas de riesgo (IA).
     */
    public function mapaPredictivo(Request $request)
    {
        $idBarrio = $request->integer('barrio_id') ?: null;

        return response()->json(
            $this->predictions->datosMapaPredictivo($idBarrio)
        );
    }
}