<?php

namespace App\Services;

use App\Models\ConfiguracionSistema;
use App\Models\Inhabilitacion;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class FalseReportService
{
    public function __construct(
        private AuditService $audit
    ) {}

    /**
     * Verifica si el usuario puede reportar incidentes.
     */
    public function puedeReportar(int $idUsuario): bool
    {
        $usuario = Usuario::find($idUsuario);
        if (!$usuario || $usuario->bloqueado || !$usuario->activo) {
            return false;
        }

        return !Inhabilitacion::where('id_usuario', $idUsuario)
            ->activas()
            ->exists();
    }

    /**
     * Incrementa contador de falsos y inhabilita si supera el límite mensual.
     */
    public function registrarFalsoReporte(int $idUsuario, ?int $idValidador = null): void
    {
        $limite = (int) ConfiguracionSistema::obtener('max_reportes_falsos_mes', 10);
        $diasInhabilitacion = (int) ConfiguracionSistema::obtener('dias_inhabilitacion_falsos', 30);

        $anio = now()->year;
        $mes = now()->month;

        DB::statement('
            INSERT INTO metricas_reportes_falsos (id_usuario, anio, mes, cantidad_falsos)
            VALUES (?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE cantidad_falsos = cantidad_falsos + 1
        ', [$idUsuario, $anio, $mes]);

        $cantidad = (int) DB::table('metricas_reportes_falsos')
            ->where('id_usuario', $idUsuario)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->value('cantidad_falsos');

        if ($cantidad >= $limite) {
            Inhabilitacion::create([
                'id_usuario' => $idUsuario,
                'fecha_inicio' => now(),
                'fecha_fin' => now()->addDays($diasInhabilitacion),
                'motivo' => "Superó {$limite} reportes falsos en el mes {$mes}/{$anio}",
                'cantidad_reportes_falsos' => $cantidad,
                'id_usuario_creador' => $idValidador,
            ]);

            Usuario::where('id_usuario', $idUsuario)->update(['bloqueado' => true]);

            $this->audit->registrar(
                'INHABILITAR_USUARIO',
                $idValidador,
                'usuarios',
                $idUsuario,
                null,
                ['cantidad_falsos' => $cantidad, 'limite' => $limite]
            );
        }
    }

    public function mensajeBloqueo(int $idUsuario): ?string
    {
        $inhabilitacion = Inhabilitacion::where('id_usuario', $idUsuario)
            ->activas()
            ->latest('fecha_inicio')
            ->first();

        if ($inhabilitacion) {
            return 'Tu cuenta está temporalmente inhabilitada por reportes falsos hasta '
                .$inhabilitacion->fecha_fin->format('d/m/Y H:i').'.';
        }

        $usuario = Usuario::find($idUsuario);
        if ($usuario?->bloqueado) {
            return 'Tu cuenta está bloqueada. Contacta a la junta vecinal.';
        }

        return null;
    }
}
