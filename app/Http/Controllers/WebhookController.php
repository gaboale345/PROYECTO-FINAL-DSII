<?php

namespace App\Http\Controllers;

use App\Models\Barrio;
use App\Models\TipoDelito;
use App\Models\Usuario;
use App\Services\AuditService;
use App\Services\DuplicateDetectionService;
use App\Services\UsuarioSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function __construct(
        private AuditService $audit,
        private DuplicateDetectionService $duplicates,
        private UsuarioSyncService $usuarioSync
    ) {}

    /**
     * Verificación webhook WhatsApp Business API.
     */
    public function whatsappVerify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === config('scsp.whatsapp.verify_token')) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Recibe mensajes de WhatsApp y crea incidentes.
     */
    public function whatsappMessage(Request $request)
    {
        $payload = $request->all();
        $this->audit->registrar('WHATSAPP_WEBHOOK', null, 'webhooks', null, $request, [
            'entries' => count($payload['entry'] ?? []),
        ]);

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $messages = $change['value']['messages'] ?? [];
                foreach ($messages as $message) {
                    $this->procesarMensajeWhatsApp($message, $change['value'] ?? []);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function procesarMensajeWhatsApp(array $message, array $value): void
    {
        if (($message['type'] ?? '') !== 'text') {
            return;
        }

        $telefono = $message['from'] ?? null;
        $texto = strtolower($message['text']['body'] ?? '');

        if (!$telefono || strlen($texto) < 5) {
            return;
        }

        $usuario = Usuario::where('telefono', 'like', '%'.substr($telefono, -8).'%')->first();
        if (!$usuario) {
            return;
        }

        $tipo = $this->detectarTipoDelito($texto);
        $barrio = Barrio::find($usuario->id_barrio);
        $lat = $barrio?->latitud_centro ?? -17.7833;
        $lng = $barrio?->longitud_centro ?? -63.1667;

        $incidente = \App\Models\Incidente::create([
            'id_usuario_reportante' => $usuario->id_usuario,
            'id_tipo_delito' => $tipo,
            'descripcion' => $message['text']['body'],
            'latitud' => $lat,
            'longitud' => $lng,
            'fecha_hora' => now(),
            'estado' => \App\Models\Incidente::ESTADO_PENDIENTE,
            'es_whatsapp' => true,
            'ip_reportante' => 'whatsapp',
        ]);

        $this->duplicates->procesarIncidenteNuevo($incidente, $usuario->id_usuario);
    }

    private function detectarTipoDelito(string $texto): int
    {
        $mapa = [
            'robo' => 'Robo',
            'asalto' => 'Asalto',
            'hurto' => 'Hurto',
            'vandalismo' => 'Vandalismo',
            'violencia' => 'Violencia',
            'emergencia' => 'Emergencia',
        ];

        foreach ($mapa as $palabra => $nombre) {
            if (str_contains($texto, $palabra)) {
                $tipo = TipoDelito::where('nombre', 'like', "%{$nombre}%")->first();

                return $tipo?->id_tipo_delito ?? TipoDelito::value('id_tipo_delito') ?? 1;
            }
        }

        return TipoDelito::value('id_tipo_delito') ?? 1;
    }
}
