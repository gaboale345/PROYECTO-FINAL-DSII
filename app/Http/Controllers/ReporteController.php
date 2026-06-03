<?php

namespace App\Http\Controllers;

use App\Models\Barrio;
use App\Models\Incidente;
use App\Models\TipoDelito;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReporteController extends Controller
{
    public function __construct(
        private AuditService $audit
    ) {}

    /**
     * Reporte por rango de fechas y barrio (HTML imprimible / PDF).
     */
    public function mensual(Request $request, ?int $barrioId = null)
    {
        $request->validate([
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
            'barrio_id' => 'nullable|integer|exists:barrios,id_barrio',
            'mes' => 'nullable|integer|min:1|max:12',
            'anio' => 'nullable|integer|min:2000|max:2100',
        ]);

        [$fechaDesde, $fechaHasta] = $this->resolverRangoFechas($request);
        $barrioId = $barrioId ?? ($request->filled('barrio_id') ? $request->integer('barrio_id') : null);

        $barrio = $barrioId ? Barrio::find($barrioId) : null;
        $barrios = Barrio::where('activo', true)->orderBy('nombre')->get();

        $estadisticas = $this->obtenerEstadisticas($barrioId, $fechaDesde, $fechaHasta);

        $this->audit->registrar(
            'GENERAR_REPORTE_MENSUAL',
            auth()->user()?->id_usuario,
            'reportes',
            $barrioId,
            $request,
            [
                'fecha_desde' => $fechaDesde->toDateString(),
                'fecha_hasta' => $fechaHasta->toDateString(),
            ]
        );

        $paramsVista = compact('barrio', 'barrios', 'estadisticas', 'fechaDesde', 'fechaHasta', 'barrioId');

        if ($request->get('formato') === 'pdf') {
            $nombre = 'reporte-'.$fechaDesde->format('Y-m-d').'_'.$fechaHasta->format('Y-m-d').'.pdf';

            return $this->descargarPdf('reportes.mensual', $paramsVista, $nombre);
        }

        return view('reportes.mensual', $paramsVista);
    }

    /**
     * Reporte semanal (mismo selector de fechas personalizado).
     */
    public function semanal(Request $request, ?int $barrioId = null)
    {
        $request->validate([
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
            'barrio_id' => 'nullable|integer|exists:barrios,id_barrio',
        ]);

        if (!$request->filled('fecha_desde') && !$request->filled('fecha_hasta')) {
            $fechaDesde = now()->startOfWeek()->startOfDay();
            $fechaHasta = now()->endOfWeek()->endOfDay();
        } else {
            [$fechaDesde, $fechaHasta] = $this->resolverRangoFechas($request);
        }

        $barrioId = $barrioId ?? ($request->filled('barrio_id') ? $request->integer('barrio_id') : null);
        $barrio = $barrioId ? Barrio::find($barrioId) : null;
        $barrios = Barrio::where('activo', true)->orderBy('nombre')->get();

        $estadisticas = $this->obtenerEstadisticas($barrioId, $fechaDesde, $fechaHasta);

        $paramsVista = compact('barrio', 'barrios', 'estadisticas', 'fechaDesde', 'fechaHasta', 'barrioId');

        if ($request->get('formato') === 'pdf') {
            $nombre = 'reporte-semanal-'.$fechaDesde->format('Y-m-d').'_'.$fechaHasta->format('Y-m-d').'.pdf';

            return $this->descargarPdf('reportes.semanal', $paramsVista, $nombre);
        }

        return view('reportes.semanal', $paramsVista);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolverRangoFechas(Request $request): array
    {
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            return [
                Carbon::parse($request->fecha_desde)->startOfDay(),
                Carbon::parse($request->fecha_hasta)->endOfDay(),
            ];
        }

        $mes = $request->integer('mes') ?: now()->month;
        $anio = $request->integer('anio') ?: now()->year;

        return [
            Carbon::create($anio, $mes, 1)->startOfMonth()->startOfDay(),
            Carbon::create($anio, $mes, 1)->endOfMonth()->endOfDay(),
        ];
    }

    private function obtenerEstadisticas(?int $barrioId, Carbon $fechaDesde, Carbon $fechaHasta): array
    {
        $query = Incidente::query()
            ->whereBetween('fecha_hora', [$fechaDesde, $fechaHasta])
            ->where('validado', true)
            ->where('es_falso_reporte', false);

        if ($barrioId) {
            $query->whereHas('reportante', fn ($q) => $q->where('id_barrio', $barrioId));
        }

        $total = (clone $query)->count();
        $falsos = Incidente::query()
            ->whereBetween('fecha_hora', [$fechaDesde, $fechaHasta])
            ->where('es_falso_reporte', true)
            ->when($barrioId, fn ($q) => $q->whereHas('reportante', fn ($r) => $r->where('id_barrio', $barrioId)))
            ->count();

        $porTipo = TipoDelito::select('tipos_delito.nombre', DB::raw('COUNT(incidentes.id_incidente) as total'))
            ->leftJoin('incidentes', function ($join) use ($fechaDesde, $fechaHasta, $barrioId) {
                $join->on('tipos_delito.id_tipo_delito', '=', 'incidentes.id_tipo_delito')
                    ->whereBetween('incidentes.fecha_hora', [$fechaDesde, $fechaHasta])
                    ->where('incidentes.validado', true)
                    ->where('incidentes.es_falso_reporte', false);
                if ($barrioId) {
                    $join->join('usuarios', 'incidentes.id_usuario_reportante', '=', 'usuarios.id_usuario')
                        ->where('usuarios.id_barrio', $barrioId);
                }
            })
            ->groupBy('tipos_delito.id_tipo_delito', 'tipos_delito.nombre')
            ->orderByDesc('total')
            ->get();

        $porDia = (clone $query)
            ->select(DB::raw('DATE(fecha_hora) as dia'), DB::raw('COUNT(*) as total'))
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        return compact('total', 'falsos', 'porTipo', 'porDia');
    }

    private function descargarPdf(string $view, array $data, string $filename)
    {
        try {
            $html = view($view, array_merge($data, ['pdf' => true]))->render();

            return Pdf::loadHTML($html)
                ->setPaper('a4', 'portrait')
                ->download($filename);
        } catch (\Throwable $e) {
            Log::error('Error al generar PDF de reporte', [
                'view' => $view,
                'error' => $e->getMessage(),
            ]);

            $ruta = $view === 'reportes.semanal' ? 'reportes.semanal' : 'reportes.mensual';

            return redirect()->route($ruta, array_filter([
                'fecha_desde' => $data['fechaDesde']->format('Y-m-d'),
                'fecha_hasta' => $data['fechaHasta']->format('Y-m-d'),
                'barrio_id' => $data['barrioId'] ?? null,
            ], fn ($v) => $v !== null && $v !== ''))
                ->with('error', 'No se pudo generar el PDF. Verifica que DomPDF esté instalado (composer install) o usa Imprimir.');
        }
    }
}
