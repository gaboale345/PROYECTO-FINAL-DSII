<?php

namespace App\Services;

use App\Models\AlertaPredictiva;
use App\Models\Barrio;
use App\Models\ConfiguracionSistema;
use App\Models\Incidente;
use App\Models\TipoDelito;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PredictionService
{
    public function __construct(
        private NotificationService $notifications,
        private AuditService $audit
    ) {}

    /**
     * Genera predicciones por barrio y hora usando datos históricos + modelo ML si existe.
     */
    public function generarPredicciones(): int
    {
        $umbral = (float) ConfiguracionSistema::obtener('umbral_probabilidad_alerta', 0.70);
        $generadas = 0;

        AlertaPredictiva::where('activa', true)->update(['activa' => false]);

        $tipoDefault = TipoDelito::value('id_tipo_delito') ?? 1;

        foreach (Barrio::where('activo', true)->get() as $barrio) {
            for ($hora = 0; $hora < 24; $hora += 2) {
                $probabilidad = $this->calcularProbabilidad($barrio->id_barrio, $hora);

                if ($probabilidad < $umbral) {
                    continue;
                }

                $nivel = $probabilidad >= 0.85 ? 'critico' : ($probabilidad >= 0.75 ? 'alto' : 'medio');
                $franja = sprintf('%02d:00-%02d:59', $hora, min($hora + 1, 23));

                $alerta = AlertaPredictiva::create([
                    'id_barrio' => $barrio->id_barrio,
                    'id_tipo_delito' => $tipoDefault,
                    'franja_horaria' => $franja,
                    'nivel_riesgo' => $nivel,
                    'probabilidad' => round($probabilidad, 4),
                    'activa' => true,
                    'fecha_generacion' => now(),
                    'fecha_expiracion' => now()->addHours(6),
                ]);

                $this->notifications->enviarAlertaBarrio($barrio->id_barrio, $alerta->mensaje);
                $generadas++;
            }
        }

        $this->audit->registrar('GENERAR_PREDICCIONES', null, 'alertas_predictivas', null, null, [
            'total' => $generadas,
        ]);

        return $generadas;
    }

    /**
     * Calcula probabilidad: intenta modelo Python, fallback heurístico histórico.
     */
    public function calcularProbabilidad(int $idBarrio, int $hora): float
    {
        $mlProb = $this->inferirConModelo($idBarrio, $hora);
        if ($mlProb !== null) {
            return $mlProb;
        }

        return $this->heuristicaHistorica($idBarrio, $hora);
    }

    private function inferirConModelo(int $idBarrio, int $hora): ?float
    {
        $modelPath = base_path('ml/modelo_predictivo.json');
        $scriptPath = base_path('ml/predict.py');

        if (!file_exists($modelPath) || !file_exists($scriptPath)) {
            return null;
        }

        $diaSemana = now()->dayOfWeek;
        $cmd = sprintf(
            'python "%s" %d %d %d "%s" 2>&1',
            $scriptPath,
            $idBarrio,
            $hora,
            $diaSemana,
            $modelPath
        );

        $output = shell_exec($cmd);
        if ($output === null) {
            return null;
        }

        $prob = (float) trim($output);
        if ($prob >= 0 && $prob <= 1) {
            return $prob;
        }

        Log::warning('Predicción ML inválida', ['output' => $output]);

        return null;
    }

    private function heuristicaHistorica(int $idBarrio, int $hora): float
    {
        $total = Incidente::whereHas('reportante', fn ($q) => $q->where('id_barrio', $idBarrio))
            ->where('fecha_hora', '>=', now()->subDays(90))
            ->where('validado', true)
            ->count();

        if ($total === 0) {
            return 0.1;
        }

        $enHora = Incidente::whereHas('reportante', fn ($q) => $q->where('id_barrio', $idBarrio))
            ->where('fecha_hora', '>=', now()->subDays(90))
            ->where('validado', true)
            ->whereRaw('HOUR(fecha_hora) BETWEEN ? AND ?', [$hora, $hora + 1])
            ->count();

        $prob = min(0.95, ($enHora / max(1, $total)) * 5 + 0.15);

        // Incremento nocturno (después de las 20:00)
        if ($hora >= 20 || $hora <= 5) {
            $prob = min(0.95, $prob + 0.15);
        }

        return round($prob, 4);
    }

    /**
     * Datos para mapa predictivo (API).
     */
    public function datosMapaPredictivo(?int $idBarrio = null): array
    {
        $query = AlertaPredictiva::with('barrio')
            ->where('activa', true)
            ->where('probabilidad', '>=', (float) ConfiguracionSistema::obtener('umbral_probabilidad_alerta', 0.70));

        if ($idBarrio) {
            $query->where('id_barrio', $idBarrio);
        }

        $alertas = $query->get();

        $features = [];
        foreach ($alertas as $alerta) {
            if (!$alerta->barrio) {
                continue;
            }

            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                        (float) $alerta->barrio->longitud_centro,
                        (float) $alerta->barrio->latitud_centro,
                    ],
                ],
                'properties' => [
                    'barrio' => $alerta->barrio->nombre,
                    'nivel_riesgo' => $alerta->nivel_riesgo,
                    'probabilidad' => $alerta->probabilidad,
                    'mensaje' => $alerta->mensaje,
                    'franja_horaria' => $alerta->franja_horaria,
                ],
            ];
        }

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
            'total' => count($features),
        ];
    }
}
