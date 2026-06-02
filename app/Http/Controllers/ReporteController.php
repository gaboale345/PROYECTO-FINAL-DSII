<?php

namespace App\Http\Controllers;

use App\Models\Barrio;
use App\Models\Incidente;
use App\Models\TipoDelito;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function __construct(
        private AuditService $audit
    ) {}

    /**
     * Reporte mensual por barrio (HTML imprimible / PDF).
     */
    public function mensual(Request $request, ?int $barrioId = null)
    {
        $barrioId = $barrioId ?? $request->integer('barrio_id');
        $mes = $request->integer('mes') ?: now()->month;
        $anio = $request->integer('anio') ?: now()->year;

        $barrio = $barrioId ? Barrio::find($barrioId) : null;
        $barrios = Barrio::where('activo', true)->orderBy('nombre')->get();

        $estadisticas = $this->obtenerEstadisticas($barrioId, $mes, $anio);

        $this->audit->registrar(
            'GENERAR_REPORTE_MENSUAL',
            auth()->user()?->id_usuario,
            'reportes',
            $barrioId,
            $request,
            ['mes' => $mes, 'anio' => $anio]
        );

        if ($request->get('formato') === 'pdf') {
            return $this->descargarPdf('reportes.mensual', compact(
                'barrio', 'barrios', 'estadisticas', 'mes', 'anio'
            ), "reporte-mensual-{$mes}-{$anio}.pdf");
        }

        return view('reportes.mensual', compact('barrio', 'barrios', 'estadisticas', 'mes', 'anio'));
    }

    /**
     * Reporte semanal por barrio.
     */
    public function semanal(Request $request, ?int $barrioId = null)
    {
        $barrioId = $barrioId ?? $request->integer('barrio_id');
        $inicio = $request->date('inicio') ?? now()->startOfWeek();
        $fin = $request->date('fin') ?? now()->endOfWeek();

        $barrio = $barrioId ? Barrio::find($barrioId) : null;
        $barrios = Barrio::where('activo', true)->orderBy('nombre')->get();

        $estadisticas = $this->obtenerEstadisticasSemana($barrioId, $inicio, $fin);

        if ($request->get('formato') === 'pdf') {
            return $this->descargarPdf('reportes.semanal', compact(
                'barrio', 'barrios', 'estadisticas', 'inicio', 'fin'
            ), 'reporte-semanal.pdf');
        }

        return view('reportes.semanal', compact('barrio', 'barrios', 'estadisticas', 'inicio', 'fin'));
    }

    private function obtenerEstadisticas(?int $barrioId, int $mes, int $anio): array
    {
        $query = Incidente::query()
            ->whereMonth('fecha_hora', $mes)
            ->whereYear('fecha_hora', $anio)
            ->where('validado', true)
            ->where('es_falso_reporte', false);

        if ($barrioId) {
            $query->whereHas('reportante', fn ($q) => $q->where('id_barrio', $barrioId));
        }

        $total = (clone $query)->count();
        $falsos = Incidente::whereMonth('fecha_hora', $mes)
            ->whereYear('fecha_hora', $anio)
            ->where('es_falso_reporte', true)
            ->when($barrioId, fn ($q) => $q->whereHas('reportante', fn ($r) => $r->where('id_barrio', $barrioId)))
            ->count();

        $porTipo = TipoDelito::select('tipos_delito.nombre', DB::raw('COUNT(incidentes.id_incidente) as total'))
            ->leftJoin('incidentes', function ($join) use ($mes, $anio, $barrioId) {
                $join->on('tipos_delito.id_tipo_delito', '=', 'incidentes.id_tipo_delito')
                    ->whereMonth('incidentes.fecha_hora', $mes)
                    ->whereYear('incidentes.fecha_hora', $anio)
                    ->where('incidentes.validado', true);
                if ($barrioId) {
                    $join->join('usuarios', 'incidentes.id_usuario_reportante', '=', 'usuarios.id_usuario')
                        ->where('usuarios.id_barrio', $barrioId);
                }
            })
            ->groupBy('tipos_delito.id_tipo_delito', 'tipos_delito.nombre')
            ->orderByDesc('total')
            ->get();

        $porDia = (clone $query)
            ->select(DB::raw('DAY(fecha_hora) as dia'), DB::raw('COUNT(*) as total'))
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        return compact('total', 'falsos', 'porTipo', 'porDia');
    }

    private function obtenerEstadisticasSemana(?int $barrioId, $inicio, $fin): array
    {
        $query = Incidente::query()
            ->whereBetween('fecha_hora', [$inicio, $fin])
            ->where('validado', true)
            ->where('es_falso_reporte', false);

        if ($barrioId) {
            $query->whereHas('reportante', fn ($q) => $q->where('id_barrio', $barrioId));
        }

        return [
            'total' => (clone $query)->count(),
            'porTipo' => TipoDelito::select('tipos_delito.nombre', DB::raw('COUNT(incidentes.id_incidente) as total'))
                ->leftJoin('incidentes', function ($join) use ($inicio, $fin, $barrioId) {
                    $join->on('tipos_delito.id_tipo_delito', '=', 'incidentes.id_tipo_delito')
                        ->whereBetween('incidentes.fecha_hora', [$inicio, $fin])
                        ->where('incidentes.validado', true);
                    if ($barrioId) {
                        $join->join('usuarios', 'incidentes.id_usuario_reportante', '=', 'usuarios.id_usuario')
                            ->where('usuarios.id_barrio', $barrioId);
                    }
                })
                ->groupBy('tipos_delito.id_tipo_delito', 'tipos_delito.nombre')
                ->orderByDesc('total')
                ->get(),
        ];
    }

    private function descargarPdf(string $view, array $data, string $filename)
    {
        $html = view($view, array_merge($data, ['pdf' => true]))->render();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->download($filename);
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'inline; filename="'.str_replace('.pdf', '.html', $filename).'"',
        ]);
    }
}
