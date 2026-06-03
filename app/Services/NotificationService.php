<?php

namespace App\Services;

use App\Models\NotificacionEnviada;
use App\Models\SuscripcionBarrio;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Envía alerta a vecinos suscritos al barrio (WhatsApp/Telegram/Web).
     */
    public function enviarAlertaBarrio(int $idBarrio, string $mensaje): int
    {
        $suscripciones = SuscripcionBarrio::where('id_barrio', $idBarrio)
            ->where('activo', true)
            ->with('usuario')
            ->get();

        $enviados = 0;
        foreach ($suscripciones as $suscripcion) {
            $idCanal = (int) (\DB::table('preferencias_usuario')
                ->where('id_usuario', $suscripcion->id_usuario)
                ->where('activo', true)
                ->value('id_canal') ?? 1);

            if ($this->enviarNotificacion($suscripcion->id_usuario, $mensaje, $idCanal)) {
                $enviados++;
            }
        }

        return $enviados;
    }

    public function enviarNotificacion(int $idUsuario, string $mensaje, int $idCanal = 1): bool
    {
        $canal = config("scsp.canales.{$idCanal}", 'web');
        $enviado = match ($canal) {
            'whatsapp' => $this->enviarWhatsApp($idUsuario, $mensaje),
            'telegram' => $this->enviarTelegram($idUsuario, $mensaje),
            default => true,
        };

        NotificacionEnviada::create([
            'id_usuario' => $idUsuario,
            'id_canal' => $idCanal,
            'titulo' => 'Alerta Santa Cruz Segura',
            'mensaje' => $mensaje,
            'leida' => false,
            'fecha_hora_envio' => now(),
            'error_envio' => $enviado ? null : 'Error al enviar por canal externo',
        ]);

        return $enviado;
    }

    private function enviarWhatsApp(int $idUsuario, string $mensaje): bool
    {
        $token = config('scsp.whatsapp.token');
        $phoneId = config('scsp.whatsapp.phone_id');

        if (!$token || !$phoneId) {
            Log::info('WhatsApp no configurado, alerta registrada en BD', compact('idUsuario', 'mensaje'));

            return true;
        }

        try {
            $telefono = $this->obtenerTelefonoUsuario($idUsuario);
            if (!$telefono) {
                return false;
            }

            $response = Http::withToken($token)->post(
                "https://graph.facebook.com/v18.0/{$phoneId}/messages",
                [
                    'messaging_product' => 'whatsapp',
                    'to' => $telefono,
                    'type' => 'text',
                    'text' => ['body' => $mensaje],
                ]
            );

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Error WhatsApp', ['error' => $e->getMessage()]);

            return false;
        }
    }

    private function enviarTelegram(int $idUsuario, string $mensaje): bool
    {
        $token = config('scsp.telegram.bot_token');
        $chatId = config('scsp.telegram.default_chat_id');

        if (!$token) {
            Log::info('Telegram no configurado, alerta registrada en BD', compact('idUsuario', 'mensaje'));

            return true;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $mensaje,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Error Telegram', ['error' => $e->getMessage()]);

            return false;
        }
    }

    private function obtenerTelefonoUsuario(int $idUsuario): ?string
    {
        $usuario = \App\Models\Usuario::find($idUsuario);
        if (!$usuario?->telefono) {
            return null;
        }

        return $usuario->telefono;
    }
}
