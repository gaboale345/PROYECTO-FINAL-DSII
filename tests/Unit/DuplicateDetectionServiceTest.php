<?php

namespace Tests\Unit;

use App\Models\ConfiguracionSistema;
use App\Services\DuplicateDetectionService;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateDetectionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_configuracion_duplicados_tiene_valores_por_defecto(): void
    {
        $this->seed(\Database\Seeders\ConfiguracionSeeder::class);

        $this->assertEquals('50', ConfiguracionSistema::obtener('radio_duplicados_metros'));
        $this->assertEquals('10', ConfiguracionSistema::obtener('tiempo_duplicados_minutos'));
    }

    public function test_fusionar_todos_retorna_cero_sin_incidentes(): void
    {
        $this->seed(\Database\Seeders\ConfiguracionSeeder::class);

        $service = app(DuplicateDetectionService::class);
        $this->assertSame(0, $service->fusionarTodos());
    }
}
