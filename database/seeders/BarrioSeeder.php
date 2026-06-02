<?php

namespace Database\Seeders;

use App\Models\Barrio;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BarrioSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $barrios = [
            ['nombre' => 'Plan 3.000', 'distrito' => 'Zona Sur', 'latitud_centro' => -17.8120, 'longitud_centro' => -63.1950],
            ['nombre' => 'Los Lotes', 'distrito' => 'Zona Sur', 'latitud_centro' => -17.8250, 'longitud_centro' => -63.2100],
            ['nombre' => 'El Remanso', 'distrito' => 'Zona Sur', 'latitud_centro' => -17.8350, 'longitud_centro' => -63.1850],
            ['nombre' => 'Villa 1° de Mayo', 'distrito' => 'Zona Sur', 'latitud_centro' => -17.7980, 'longitud_centro' => -63.1780],
            ['nombre' => 'Palmar del Oratorio', 'distrito' => 'Zona Sur', 'latitud_centro' => -17.8050, 'longitud_centro' => -63.1650],
            ['nombre' => 'Zona Sur - Equipetrol Sur', 'distrito' => 'Zona Sur', 'latitud_centro' => -17.7700, 'longitud_centro' => -63.1700],
            ['nombre' => 'Centro Histórico', 'distrito' => 'Zona Centro', 'latitud_centro' => -17.7833, 'longitud_centro' => -63.1821],
            ['nombre' => 'La Ramada', 'distrito' => 'Zona Este', 'latitud_centro' => -17.7650, 'longitud_centro' => -63.1500],
            ['nombre' => 'Pampa de la Isla', 'distrito' => 'Zona Este', 'latitud_centro' => -17.7580, 'longitud_centro' => -63.1400],
            ['nombre' => 'Banzer', 'distrito' => 'Zona Norte', 'latitud_centro' => -17.7450, 'longitud_centro' => -63.1750],
        ];

        foreach ($barrios as $barrio) {
            Barrio::firstOrCreate(
                ['nombre' => $barrio['nombre']],
                array_merge($barrio, ['activo' => true])
            );
        }
    }
}
