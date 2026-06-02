<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id_rol' => 1, 'nombre_rol' => 'Vecino', 'descripcion' => 'Puede reportar y ver alertas de su barrio'],
            ['id_rol' => 2, 'nombre_rol' => 'Administrador Junta Vecinal', 'descripcion' => 'Valida reportes y genera estadísticas'],
            ['id_rol' => 3, 'nombre_rol' => 'Policía', 'descripcion' => 'Accede a mapas de calor y planifica operativos'],
            ['id_rol' => 4, 'nombre_rol' => 'SuperAdministrador', 'descripcion' => 'Gestiona usuarios y configuración del sistema'],
        ];

        foreach ($roles as $rol) {
            DB::table('roles')->updateOrInsert(['id_rol' => $rol['id_rol']], $rol);
        }
    }
}
