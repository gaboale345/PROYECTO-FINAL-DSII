<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            BarrioSeeder::class,
            ConfiguracionSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Super Admin SCSP',
            'email' => 'admin@scsp.local',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'id_rol' => 4,
            'id_barrio' => 1,
        ]);

        User::factory()->create([
            'name' => 'Junta Vecinal Plan 3000',
            'email' => 'junta@scsp.local',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'id_rol' => 2,
            'id_barrio' => 1,
        ]);
    }
}
