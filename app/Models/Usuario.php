<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Model
{
    /**
     * Tabla asociada
     */
    protected $table = 'usuarios';
    
    /**
     * Llave primaria
     */
    protected $primaryKey = 'id_usuario';
    
    /**
     * No usamos timestamps (nuestra tabla no tiene created_at/updated_at)
     */
    public $timestamps = false;
    
    /**
     * Atributos que se pueden llenar masivamente
     */
    protected $fillable = [
        'nombre_completo',
        'email',
        'telefono',
        'hash_password',
        'id_rol',
        'id_barrio',
        'activo',
        'bloqueado',
        'ultimo_acceso'
    ];
    
    /**
     * Atributos ocultos para arrays/JSON (como la contraseña)
     */
    protected $hidden = [
        'hash_password',
        'token_recuperacion'
    ];
    
    /**
     * Casting de atributos
     */
    protected $casts = [
        'activo' => 'boolean',
        'bloqueado' => 'boolean',
        'fecha_registro' => 'datetime',
        'ultimo_acceso' => 'datetime',
        'telefono' => 'encrypted',
    ];
    
    // ==============================================
    // RELACIONES
    // ==============================================
    
    /**
     * Relación: Pertenece a un rol
     * Un usuario tiene UN rol (Vecino, Admin, Policía, SuperAdmin)
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }
    
    /**
     * Relación: Pertenece a un barrio
     * Un usuario vive en UN barrio
     */
    public function barrio(): BelongsTo
    {
        return $this->belongsTo(Barrio::class, 'id_barrio');
    }
    
    /**
     * Relación: Un usuario reporta muchos incidentes
     * (como reportante)
     */
    public function incidentesReportados(): HasMany
    {
        return $this->hasMany(Incidente::class, 'id_usuario_reportante');
    }
    
    /**
     * Relación: Un usuario valida muchos incidentes
     * (como validador, solo Admins y Policía)
     */
    public function incidentesValidados(): HasMany
    {
        return $this->hasMany(Incidente::class, 'id_usuario_validador');
    }
    
    /**
     * Relación: Un usuario es testigo de muchos incidentes
     * (a través de la tabla pivote testigos_incidente)
     */
    public function incidentesComoTestigo(): HasMany
    {
        return $this->hasMany(TestigoIncidente::class, 'id_usuario_testigo');
    }
    
    /**
     * Relación: Un usuario recibe muchas notificaciones
     */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(NotificacionEnviada::class, 'id_usuario');
    }
    
    /**
     * Relación: Un usuario tiene muchas inhabilitaciones
     */
    public function inhabilitaciones(): HasMany
    {
        return $this->hasMany(Inhabilitacion::class, 'id_usuario');
    }
    
    // ==============================================
    // MÉTODOS DE UTILIDAD
    // ==============================================
    
    /**
     * Verifica si el usuario es SuperAdmin
     */
    public function esSuperAdmin(): bool
    {
        return $this->id_rol == 4; // SuperAdmin tiene id_rol = 4
    }
    
    /**
     * Verifica si el usuario es Administrador de Junta Vecinal
     */
    public function esAdminJunta(): bool
    {
        return $this->id_rol == 2;
    }
    
    /**
     * Verifica si el usuario es Policía
     */
    public function esPolicia(): bool
    {
        return $this->id_rol == 3;
    }
    
    /**
     * Verifica si el usuario es Vecino
     */
    public function esVecino(): bool
    {
        return $this->id_rol == 1;
    }
    
    /**
     * Verifica si el usuario está bloqueado
     */
    public function estaBloqueado(): bool
    {
        return $this->bloqueado == true;
    }
    
    /**
     * Obtener el nombre del rol como string
     */
    public function getNombreRolAttribute(): string
    {
        return $this->rol->nombre_rol ?? 'Sin rol';
    }
}