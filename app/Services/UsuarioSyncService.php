<?php

namespace App\Services;

use App\Models\User;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UsuarioSyncService
{
    /**
     * Crea o sincroniza el registro en `usuarios` vinculado al User de Laravel.
     */
    public function sincronizarDesdeUser(User $user, array $extra = []): Usuario
    {
        if ($user->id_usuario) {
            $usuario = Usuario::find($user->id_usuario);
            if ($usuario) {
                return $usuario;
            }
        }

        $usuario = Usuario::updateOrCreate(
            ['email' => $user->email],
            [
                'nombre_completo' => $user->name,
                'hash_password' => $user->password,
                'id_rol' => $extra['id_rol'] ?? $user->id_rol ?? 1,
                'id_barrio' => $extra['id_barrio'] ?? $user->id_barrio,
                'telefono' => $extra['telefono'] ?? null,
                'activo' => true,
                'bloqueado' => false,
                'fecha_registro' => now(),
            ]
        );

        if (!$user->id_usuario) {
            $user->update(['id_usuario' => $usuario->id_usuario]);
        }

        return $usuario;
    }

    public function idUsuarioAutenticado(): ?int
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        if ($user->id_usuario) {
            return $user->id_usuario;
        }

        return $this->sincronizarDesdeUser($user)->id_usuario;
    }
}
