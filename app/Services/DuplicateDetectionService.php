<?php

namespace App\Services;

use App\Models\ConfiguracionSistema;
use App\Models\Incidente;
use App\Models\TestigoIncidente;
use Illuminate\Support\Facades\DB;

class DuplicateDetectionService
{
    public function __construct(
        private AuditService $audit
    ) {}

    /**
     * Busca duplicados cercanos y fusiona el incidente recién creado si aplica.
     *
     * @return array{fusionado: bool, id_principal: ?int}
     */
    public function procesarIncidenteNuevo(Incidente $incidente, ?int $idUsuario = null): array
    {
        $radio = (float) (ConfiguracionSistema::obtener('radio_duplicados_metros', 50));
        $minutos = (int) (ConfiguracionSistema::obtener('tiempo_duplicados_minutos', 10));

        $principal = $this->buscarDuplicado($incidente, $radio, $minutos);

        if (!$principal) {
            return ['fusionado' => false, 'id_principal' => null];
        }

        DB::transaction(function () use ($incidente, $principal, $idUsuario) {
            $incidente->update([
                'id_incidente_principal' => $principal->id_incidente,
                'estado' => Incidente::ESTADO_FUSIONADO,
            ]);

            TestigoIncidente::firstOrCreate(
                [
                    'id_incidente' => $principal->id_incidente,
                    'id_usuario_testigo' => $incidente->id_usuario_reportante,
                ],
                [
                    'fecha_testimonio' => now(),
                    'comentario' => 'Reporte fusionado automáticamente (duplicado detectado)',
                ]
            );

            $this->audit->registrar(
                'FUSIONAR_DUPLICADO',
                $idUsuario,
                'incidentes',
                $incidente->id_incidente,
                null,
                [
                    'id_principal' => $principal->id_incidente,
                    'id_duplicado' => $incidente->id_incidente,
                ]
            );
        });

        return ['fusionado' => true, 'id_principal' => $principal->id_incidente];
    }

    /**
     * Fusiona todos los duplicados pendientes (tarea programada).
     */
    public function fusionarTodos(): int
    {
        $radio = (float) (ConfiguracionSistema::obtener('radio_duplicados_metros', 50));
        $minutos = (int) (ConfiguracionSistema::obtener('tiempo_duplicados_minutos', 10));

        $fusionados = 0;

        $candidatos = Incidente::whereNull('id_incidente_principal')
            ->where('estado', '!=', Incidente::ESTADO_FUSIONADO)
            ->orderBy('id_incidente')
            ->get();

        foreach ($candidatos as $incidente) {
            $incidente->refresh();
            if ($incidente->id_incidente_principal) {
                continue;
            }

            $principal = $this->buscarDuplicado($incidente, $radio, $minutos);
            if (!$principal) {
                continue;
            }

            $incidente->update([
                'id_incidente_principal' => $principal->id_incidente,
                'estado' => Incidente::ESTADO_FUSIONADO,
            ]);

            TestigoIncidente::firstOrCreate(
                [
                    'id_incidente' => $principal->id_incidente,
                    'id_usuario_testigo' => $incidente->id_usuario_reportante,
                ],
                [
                    'fecha_testimonio' => now(),
                    'comentario' => 'Fusión automática por proximidad temporal',
                ]
            );

            $fusionados++;
        }

        return $fusionados;
    }

    private function deltaLongitud(float $radioMetros, float $latitud): float
    {
        $latRad = deg2rad($latitud);

        return $radioMetros / (111320.0 * max(cos($latRad), 0.0001));
    }

    private function buscarDuplicado(Incidente $incidente, float $radio, int $minutos): ?Incidente
    {
        $deltaLon = $this->deltaLongitud($radio, (float) $incidente->latitud);

        return Incidente::where('id_tipo_delito', $incidente->id_tipo_delito)
            ->where('id_incidente', '<', $incidente->id_incidente)
            ->whereNull('id_incidente_principal')
            ->where('estado', '!=', Incidente::ESTADO_FUSIONADO)
            ->where('fecha_hora', '>=', $incidente->fecha_hora->copy()->subMinutes($minutos))
            ->where('fecha_hora', '<=', $incidente->fecha_hora->copy()->addMinutes($minutos))
            ->whereRaw('ABS(latitud - ?) <= ? / 111320.0', [$incidente->latitud, $radio])
            ->whereRaw('ABS(longitud - ?) <= ?', [$incidente->longitud, $deltaLon])
            ->orderBy('fecha_hora')
            ->first();
    }
}
