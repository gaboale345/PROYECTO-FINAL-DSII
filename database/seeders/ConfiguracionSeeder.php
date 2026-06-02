<?php

namespace Database\Seeders;

use App\Models\ConfiguracionSistema;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            ['clave' => 'radio_duplicados_metros', 'valor' => '50', 'descripcion' => 'Radio en metros para detectar duplicados'],
            ['clave' => 'tiempo_duplicados_minutos', 'valor' => '10', 'descripcion' => 'Ventana temporal para duplicados'],
            ['clave' => 'max_reportes_falsos_mes', 'valor' => '10', 'descripcion' => 'Máximo de falsos reportes antes de inhabilitar'],
            ['clave' => 'dias_inhabilitacion_falsos', 'valor' => '30', 'descripcion' => 'Días de inhabilitación por exceso de falsos'],
            ['clave' => 'max_intentos_login', 'valor' => '5', 'descripcion' => 'Intentos fallidos antes de bloqueo'],
            ['clave' => 'ventana_bloqueo_login_minutos', 'valor' => '15', 'descripcion' => 'Minutos de bloqueo tras intentos fallidos'],
            ['clave' => 'umbral_probabilidad_alerta', 'valor' => '0.70', 'descripcion' => 'Umbral mínimo de probabilidad para alertas IA'],
            ['clave' => 'rate_limit_por_segundo', 'valor' => '100', 'descripcion' => 'Peticiones máximas por segundo'],
            ['clave' => 'sla_respuesta_ms', 'valor' => '2000', 'descripcion' => 'SLA respuesta consultas normales (ms)'],
            ['clave' => 'sla_prediccion_ms', 'valor' => '5000', 'descripcion' => 'SLA generación predicciones (ms)'],
            ['clave' => 'disponibilidad_objetivo', 'valor' => '99.5', 'descripcion' => 'Disponibilidad objetivo (%)'],
            ['clave' => 'failover_minutos', 'valor' => '5', 'descripcion' => 'Tiempo máximo conmutación servidor secundario'],
        ];

        foreach ($configs as $config) {
            ConfiguracionSistema::establecer($config['clave'], $config['valor'], $config['descripcion']);
        }
    }
}
