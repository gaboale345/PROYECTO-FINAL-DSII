<?php

namespace App\Services;

use App\Models\IntentoAcceso;
use App\Models\User;
use App\Models\ConfiguracionSistema;
use Illuminate\Http\Request;

class LoginSecurityService
{
    public function __construct(
        private AuditService $audit
    ) {}

    public function registrarIntento(string $email, Request $request, bool $exitoso): void
    {
        IntentoAcceso::create([
            'email' => $email,
            'ip_origen' => $request->ip(),
            'exitoso' => $exitoso,
            'fecha_hora' => now(),
        ]);

        $this->audit->registrar(
            $exitoso ? 'LOGIN_EXITOSO' : 'LOGIN_FALLIDO',
            null,
            'users',
            null,
            $request,
            ['email' => $email]
        );
    }

    public function estaBloqueado(string $email): bool
    {
        $maxIntentos = (int) ConfiguracionSistema::obtener('max_intentos_login', 5);
        $ventanaMinutos = (int) ConfiguracionSistema::obtener('ventana_bloqueo_login_minutos', 15);

        $fallidos = IntentoAcceso::where('email', $email)
            ->where('exitoso', false)
            ->where('fecha_hora', '>=', now()->subMinutes($ventanaMinutos))
            ->count();

        return $fallidos >= $maxIntentos;
    }

    public function bloquearCuenta(User $user, int $minutos = 15): void
    {
        $user->update(['bloqueado' => true, 'bloqueado_hasta' => now()->addMinutes($minutos)]);
    }

    public function cuentaBloqueada(User $user): bool
    {
        if (!$user->bloqueado) {
            return false;
        }

        if ($user->bloqueado_hasta && $user->bloqueado_hasta->isPast()) {
            $user->update(['bloqueado' => false, 'bloqueado_hasta' => null]);

            return false;
        }

        return true;
    }

    public function minutosRestantesBloqueo(User $user): int
    {
        if (!$user->bloqueado_hasta) {
            return 15;
        }

        return max(1, (int) now()->diffInMinutes($user->bloqueado_hasta, false));
    }
}
