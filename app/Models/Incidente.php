<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incidente extends Model
{
    /**
     * Tabla asociada
     */
    protected $table = 'incidentes';
    
    /**
     * Llave primaria
     */
    protected $primaryKey = 'id_incidente';
    
    /**
     * No usamos timestamps (pero tenemos fecha_hora)
     */
    public $timestamps = false;
    
    /**
     * Atributos que se pueden llenar masivamente
     */
    protected $fillable = [
        'id_usuario_reportante',
        'id_tipo_delito',
        'descripcion',
        'latitud',
        'longitud',
        'fecha_hora',
        'validado',
        'id_usuario_validador',
        'fecha_validacion',
        'es_falso_reporte',
        'id_incidente_principal',
        'estado',
        'es_anonimo',
        'es_por_voz',
        'es_whatsapp',
        'ip_reportante'
    ];
    
    /**
     * Casting de atributos
     */
    protected $casts = [
        'validado' => 'boolean',
        'es_falso_reporte' => 'boolean',
        'es_anonimo' => 'boolean',
        'es_por_voz' => 'boolean',
        'es_whatsapp' => 'boolean',
        'fecha_hora' => 'datetime',
        'fecha_validacion' => 'datetime',
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7'
    ];
    
    /**
     * Estados posibles de un incidente
     */
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EN_REVISION = 'en_revision';
    const ESTADO_CONFIRMADO = 'confirmado';
    const ESTADO_CERRADO = 'cerrado';
    const ESTADO_FUSIONADO = 'fusionado';
    
    /**
     * Devuelve lista de estados para usar en selects
     */
    public static function getEstados(): array
    {
        return [
            self::ESTADO_PENDIENTE => 'Pendiente',
            self::ESTADO_EN_REVISION => 'En revisión',
            self::ESTADO_CONFIRMADO => 'Confirmado',
            self::ESTADO_CERRADO => 'Cerrado',
            self::ESTADO_FUSIONADO => 'Fusionado',
        ];
    }
    
    // ==============================================
    // RELACIONES
    // ==============================================
    
    /**
     * Relación: El incidente fue reportado por un usuario
     */
    public function reportante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_reportante');
    }
    
    /**
     * Relación: El incidente fue validado por un usuario (admin o policía)
     */
    public function validador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_validador');
    }
    
    /**
     * Relación: El tipo de delito del incidente
     */
    public function tipoDelito(): BelongsTo
    {
        return $this->belongsTo(TipoDelito::class, 'id_tipo_delito');
    }
    
    /**
     * Relación: Este incidente es el principal de otros (fusionados)
     */
    public function incidentesFusionados(): HasMany
    {
        return $this->hasMany(Incidente::class, 'id_incidente_principal');
    }
    
    /**
     * Relación: El incidente principal (si este está fusionado)
     */
    public function incidentePrincipal(): BelongsTo
    {
        return $this->belongsTo(Incidente::class, 'id_incidente_principal');
    }
    
    /**
     * Relación: Los testigos de este incidente
     */
    public function testigos(): HasMany
    {
        return $this->hasMany(TestigoIncidente::class, 'id_incidente');
    }

    /**
     * Relación: Un incidente tiene muchas imágenes
     */
    public function imagenes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ImagenIncidente::class, 'id_incidente', 'id_incidente');
    }

    /**
     * Relación: La imagen principal del incidente
     */
    public function imagenPrincipal(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ImagenIncidente::class, 'id_incidente', 'id_incidente')->where('es_principal', true);
    }
    
    // ==============================================
    // SCOPES (consultas comunes)
    // ==============================================
    
    /**
     * Scope para incidentes validados (reales)
     */
    public function scopeValidados($query)
    {
        return $query->where('validado', true)
                     ->where('es_falso_reporte', false);
    }
    
    /**
     * Scope para incidentes pendientes de validación
     */
    public function scopePendientes($query)
    {
        return $query->where('validado', false)
                     ->where('estado', self::ESTADO_PENDIENTE);
    }
    
    /**
     * Scope para incidentes de los últimos N días
     */
    public function scopeUltimosDias($query, $dias = 30)
    {
        return $query->where('fecha_hora', '>=', now()->subDays($dias));
    }
    
    /**
     * Scope por barrio (a través del usuario reportante)
     */
    public function scopePorBarrio($query, $idBarrio)
    {
        return $query->whereHas('reportante', function($q) use ($idBarrio) {
            $q->where('id_barrio', $idBarrio);
        });
    }
    
    // ==============================================
    // MÉTODOS DE UTILIDAD
    // ==============================================
    
    /**
     * Verifica si este incidente es real (validado y no falso)
     */
    public function esReal(): bool
    {
        return $this->validado && !$this->es_falso_reporte;
    }
    
    /**
     * Verifica si está pendiente de validación
     */
    public function estaPendiente(): bool
    {
        return !$this->validado && $this->estado === self::ESTADO_PENDIENTE;
    }
    
    /**
     * Verifica si es un incidente fusionado (secundario)
     */
    public function esFusionado(): bool
    {
        return $this->id_incidente_principal !== null;
    }
    
    /**
     * Obtener la ubicación como array
     */
    public function getUbicacionAttribute(): array
    {
        return [
            'lat' => (float) $this->latitud,
            'lng' => (float) $this->longitud
        ];
    }
}