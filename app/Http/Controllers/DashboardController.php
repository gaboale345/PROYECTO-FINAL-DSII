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
        $barrios = Barrio::where('activo', true)->orderBy('nombre')->get();

        if ($request->input('barrio_id') === 'todos') {
            return view('dashboard', array_merge(
                $this->obtenerDatosDashboard(null, $barrios),
                ['barrioActual' => null, 'verTodos' => true]
            ));
        }

        $barrioActual = $this->resolverBarrioActual($request, $barrios);

        if (!$barrioActual) {
            return view('dashboard', array_merge($this->dashboardVacio($barrios), ['verTodos' => false]));
        }

        return view('dashboard', array_merge(
            $this->obtenerDatosDashboard($barrioActual->id_barrio, $barrios),
            ['barrioActual' => $barrioActual, 'verTodos' => false]
        ));
    }

    private function resolverBarrioActual(Request $request, $barrios): ?Barrio
    {
        if ($request->filled('barrio_id')) {
            return $barrios->firstWhere('id_barrio', (int) $request->barrio_id);
        }

        $idBarrioUsuario = auth()->user()?->id_barrio;
        if ($idBarrioUsuario) {
            $barrioUsuario = $barrios->firstWhere('id_barrio', (int) $idBarrioUsuario);
            if ($barrioUsuario) {
                return $barrioUsuario;
            }
        }

        return $barrios->first();
    }

    /**
     * @param  int|null  $barrioId  null = todos los barrios
     */
    private function obtenerDatosDashboard(?int $barrioId, $barrios): array
    {
        $query30d = Incidente::query()->where('fecha_hora', '>=', now()->subDays(30));
        $this->aplicarFiltroBarrio($query30d, $barrioId);
        $totalIncidentes30d = (clone $query30d)->count();

        $zonasCriticas = Barrio::whereHas('usuarios', function ($q) {
            $q->whereHas('incidentesReportados', function ($sub) {
                $sub->where('fecha_hora', '>=', now()->subDays(30))
                    ->where('validado', true);
            });
        })
            ->withCount(['usuarios as incidentes_count' => function ($q) {
                $q->whereHas('incidentesReportados', function ($sub) {
                    $sub->where('fecha_hora', '>=', now()->subDays(30))
                        ->where('validado', true);
                });
            }])
            ->having('incidentes_count', '>', 5)
            ->count();

        $participacionVecinal = Usuario::whereHas('incidentesReportados', function ($q) {
            $q->where('fecha_hora', '>=', now()->subDays(30));
        })->count();

        $periodoActualQuery = Incidente::query()
            ->whereBetween('fecha_hora', [now()->subDays(30), now()]);
        $this->aplicarFiltroBarrio($periodoActualQuery, $barrioId);
        $periodoActual = $periodoActualQuery->count();

        $periodoAnteriorQuery = Incidente::query()
            ->whereBetween('fecha_hora', [now()->subDays(60), now()->subDays(30)]);
        $this->aplicarFiltroBarrio($periodoAnteriorQuery, $barrioId);
        $periodoAnterior = $periodoAnteriorQuery->count();

        $tendencia = $periodoAnterior > 0
            ? round((($periodoActual - $periodoAnterior) / $periodoAnterior) * 100)
            : 0;

        $nivelRiesgo = $this->calcularNivelRiesgo($totalIncidentes30d);

        $incidentesMapaQuery = Incidente::with(['tipoDelito', 'reportante.barrio'])
            ->where('validado', true)
            ->where('es_falso_reporte', false)
            ->where('fecha_hora', '>=', now()->subDays(30));
        $this->aplicarFiltroBarrio($incidentesMapaQuery, $barrioId);
        $incidentesMapa = $incidentesMapaQuery->get();

        $tendenciaSemanas = [];
        for ($i = 3; $i >= 0; $i--) {
            $semanaInicio = now()->subDays(($i + 1) * 7);
            $semanaFin = now()->subDays($i * 7);

            $semanaQuery = Incidente::query()
                ->whereBetween('fecha_hora', [$semanaInicio, $semanaFin]);
            $this->aplicarFiltroBarrio($semanaQuery, $barrioId);

            $tendenciaSemanas[] = [
                'fecha' => $semanaFin->format('M d'),
                'total' => $semanaQuery->count(),
            ];
        }

        $tiposIncidentes = TipoDelito::select('tipos_delito.nombre', DB::raw('COUNT(incidentes.id_incidente) as total'))
            ->leftJoin('incidentes', function ($join) use ($barrioId) {
                $join->on('tipos_delito.id_tipo_delito', '=', 'incidentes.id_tipo_delito')
                    ->where('incidentes.fecha_hora', '>=', now()->subDays(30));

                if ($barrioId !== null) {
                    $join->join('usuarios', 'incidentes.id_usuario_reportante', '=', 'usuarios.id_usuario')
                        ->where('usuarios.id_barrio', $barrioId);
                }
            })
            ->groupBy('tipos_delito.id_tipo_delito', 'tipos_delito.nombre')
            ->get();

        $totalIncidentesTipo = $tiposIncidentes->sum('total');
        foreach ($tiposIncidentes as $tipo) {
            $tipo->porcentaje = $totalIncidentesTipo > 0
                ? round(($tipo->total / $totalIncidentesTipo) * 100)
                : 0;
        }

        return [
            'totalIncidentes30d' => $totalIncidentes30d,
            'zonasCriticas' => $zonasCriticas,
            'participacionVecinal' => $participacionVecinal,
            'tendencia' => $tendencia,
            'nivelRiesgo' => $nivelRiesgo,
            'incidentesMapa' => $incidentesMapa,
            'tendenciaSemanas' => collect($tendenciaSemanas),
            'tiposIncidentes' => $tiposIncidentes,
            'geojson' => $this->generarGeoJson($incidentesMapa),
            'barrios' => $barrios,
        ];
    }

    private function aplicarFiltroBarrio($query, ?int $barrioId): void
    {
        if ($barrioId !== null) {
            $query->whereHas('reportante', fn ($q) => $q->where('id_barrio', $barrioId));
        }
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
            'verTodos' => false,
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
                    'barrio' => $incidente->reportante?->barrio?->nombre ?? '',
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
        if ($request->input('barrio_id') === 'todos') {
            $incidentes = Incidente::with(['tipoDelito', 'reportante.barrio'])
                ->where('validado', true)
                ->where('es_falso_reporte', false)
                ->where('fecha_hora', '>=', now()->subDays(30))
                ->get();

            return response()->json([
                'barrio' => ['nombre' => 'Todos los barrios', 'id_barrio' => 'todos'],
                'total_incidentes' => $incidentes->count(),
                'geojson' => $this->generarGeoJson($incidentes),
            ]);
        }

        $barrio = Barrio::where('activo', true)->find($request->integer('barrio_id'));

        if (!$barrio) {
            return response()->json(['error' => 'Barrio no encontrado'], 404);
        }

        $incidentes = Incidente::with(['tipoDelito', 'reportante.barrio'])
            ->whereHas('reportante', fn ($q) => $q->where('id_barrio', $barrio->id_barrio))
            ->where('validado', true)
            ->where('es_falso_reporte', false)
            ->where('fecha_hora', '>=', now()->subDays(30))
            ->get();

        return response()->json([
            'barrio' => $barrio,
            'total_incidentes' => $incidentes->count(),
            'geojson' => $this->generarGeoJson($incidentes),
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