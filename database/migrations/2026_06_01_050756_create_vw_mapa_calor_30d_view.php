<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $db = DB::connection()->getDatabaseName();

        DB::statement("
            CREATE VIEW `vw_mapa_calor_30d` AS
            SELECT
                b.id_barrio,
                b.nombre AS barrio,
                td.nombre AS tipo_delito,
                COUNT(*) AS intensidad,
                AVG(i.latitud) AS latitud_promedio,
                AVG(i.longitud) AS longitud_promedio,
                HOUR(i.fecha_hora) AS hora_dia
            FROM {$db}.incidentes i
            JOIN {$db}.usuarios u ON i.id_usuario_reportante = u.id_usuario
            JOIN {$db}.barrios b ON u.id_barrio = b.id_barrio
            JOIN {$db}.tipos_delito td ON i.id_tipo_delito = td.id_tipo_delito
            WHERE i.fecha_hora >= CURRENT_TIMESTAMP - INTERVAL 30 DAY
              AND i.validado = 1
              AND i.es_falso_reporte = 0
            GROUP BY b.id_barrio, b.nombre, td.id_tipo_delito, td.nombre, HOUR(i.fecha_hora)
        ");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS `vw_mapa_calor_30d`');
    }
};
