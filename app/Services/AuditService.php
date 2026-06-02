<?php

namespace App\Services;

use App\Models\LogAuditoria;
use Illuminate\Http\Request;

class AuditService
{
    /**
     * Registra una acción en el log de auditoría (append-only).
     */
    public function registrar(
        string $accion,
        ?int $idUsuario = null,
        ?string $tabla = null,
        ?int $registroId = null,
        ?Request $request = null,
        ?array $detalles = null
    ): LogAuditoria {
        return LogAuditoria::create([
            'id_usuario' => $idUsuario,
            'accion' => $accion,
            'tabla_afectada' => $tabla,
            'registro_id' => $registroId,
            'ip_origen' => $request?->ip() ?? '0.0.0.0',
            'user_agent' => $request?->userAgent(),
            'detalles' => $detalles,
            'fecha_hora' => now(),
        ]);
    }
}
