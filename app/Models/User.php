<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'id_usuario',
        'id_rol',
        'id_barrio',
        'telefono',
        'bloqueado',
        'bloqueado_hasta',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'telefono',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'telefono' => 'encrypted',
            'bloqueado' => 'boolean',
            'bloqueado_hasta' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function barrio(): BelongsTo
    {
        return $this->belongsTo(Barrio::class, 'id_barrio', 'id_barrio');
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function esVecino(): bool
    {
        return $this->id_rol === 1;
    }

    public function esAdminJunta(): bool
    {
        return $this->id_rol === 2;
    }

    public function esPolicia(): bool
    {
        return $this->id_rol === 3;
    }

    public function esSuperAdmin(): bool
    {
        return (int) $this->id_rol === 4;
    }

    public function puedeAdministrar(): bool
    {
        return $this->esSuperAdmin();
    }

    /**
     * Solo Administrador Junta Vecinal (2) y SuperAdministrador (4).
     */
    public function puedeValidar(): bool
    {
        return in_array((int) $this->id_rol, [2, 4], true);
    }

    public function nombreRol(): string
    {
        return match ($this->id_rol) {
            2 => 'Administrador Junta Vecinal',
            3 => 'Policía',
            4 => 'SuperAdministrador',
            default => 'Vecino',
        };
    }
}
