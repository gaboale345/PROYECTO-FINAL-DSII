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
            CREATE VIEW `vw_reporte_mensual_barrio` AS
            SELECT
                b.nombre AS barrio,
                b.distrito AS distrito,
                YEAR(i.fecha_hora) AS anio,
                MONTH(i.fecha_hora) AS mes,
                td.nombre AS tipo_delito,
                COUNT(*) AS total_incidentes,
                SUM(CASE WHEN i.validado = 1 THEN 1 ELSE 0 END) AS incidentes_validados,
                SUM(CASE WHEN i.es_falso_reporte = 1 THEN 1 ELSE 0 END) AS falsos_reportes
            FROM {$db}.incidentes i
            JOIN {$db}.usuarios u ON i.id_usuario_reportante = u.id_usuario
            JOIN {$db}.barrios b ON u.id_barrio = b.id_barrio
            JOIN {$db}.tipos_delito td ON i.id_tipo_delito = td.id_tipo_delito
            GROUP BY b.id_barrio, b.nombre, b.distrito, YEAR(i.fecha_hora), MONTH(i.fecha_hora), td.id_tipo_delito, td.nombre
        ");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS `vw_reporte_mensual_barrio`');
    }
};
