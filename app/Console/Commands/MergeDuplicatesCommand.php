<?php

namespace App\Console\Commands;

use App\Services\DuplicateDetectionService;
use Illuminate\Console\Command;

class MergeDuplicatesCommand extends Command
{
    protected $signature = 'scsp:merge-duplicates';

    protected $description = 'Fusiona incidentes duplicados automáticamente';

    public function handle(DuplicateDetectionService $service): int
    {
        $total = $service->fusionarTodos();
        $this->info("Fusionados: {$total}");

        return self::SUCCESS;
    }
}
