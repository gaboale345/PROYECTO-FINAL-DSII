<?php

namespace App\Console\Commands;

use App\Services\PredictionService;
use Illuminate\Console\Command;

class GeneratePredictionsCommand extends Command
{
    protected $signature = 'scsp:generate-predictions';

    protected $description = 'Genera predicciones de riesgo y alertas para vecinos suscritos';

    public function handle(PredictionService $service): int
    {
        $start = microtime(true);
        $total = $service->generarPredicciones();
        $ms = round((microtime(true) - $start) * 1000);

        $this->info("Alertas generadas: {$total} ({$ms} ms)");

        return self::SUCCESS;
    }
}
