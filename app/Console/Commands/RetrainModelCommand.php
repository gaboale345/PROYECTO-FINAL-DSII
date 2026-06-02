<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RetrainModelCommand extends Command
{
    protected $signature = 'scsp:retrain-model';

    protected $description = 'Reentrena el modelo de IA semanalmente con scikit-learn';

    public function handle(): int
    {
        $script = base_path('ml/train_model.py');
        $python = config('scsp.ml.python_path', 'python');

        if (!file_exists($script)) {
            $this->error('Script ML no encontrado: ml/train_model.py');

            return self::FAILURE;
        }

        $cmd = sprintf('"%s" "%s" 2>&1', $python, $script);
        $output = shell_exec($cmd);

        $modelPath = base_path('ml/modelo_predictivo.json');
        $precision = 0.0;
        $metricas = [];

        if (file_exists($modelPath)) {
            $metricas = json_decode(file_get_contents($modelPath), true) ?? [];
            $precision = (float) ($metricas['precision'] ?? 0);
        }

        $version = now()->format('Y.m.d.Hi');

        DB::table('modelos_ia')->insert([
            'version' => $version,
            'precision_porcentaje' => round($precision * 100, 2),
            'fecha_entrenamiento' => now(),
            'activo' => $precision >= 0.70,
            'ruta_archivo' => $modelPath,
            'metricas_adicionales' => json_encode([
                'recall' => $metricas['recall'] ?? null,
                'f1_score' => $metricas['f1_score'] ?? null,
            ]),
            'hiperparametros' => json_encode([
                'algoritmo' => 'RandomForest',
                'features' => ['id_barrio', 'hora', 'dia_semana'],
            ]),
        ]);

        if ($precision >= 0.70) {
            DB::table('modelos_ia')->where('version', '!=', $version)->update(['activo' => false]);
        }

        $this->info("Modelo reentrenado. Precisión: ".round($precision * 100, 1)."%. {$output}");

        return $precision >= 0.70 ? self::SUCCESS : self::FAILURE;
    }
}
