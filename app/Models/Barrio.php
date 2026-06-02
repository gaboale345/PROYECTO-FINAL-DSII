<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barrio extends Model
{
    /**
     * Tabla asociada
     * Laravel buscaría 'barrios' por defecto, pero lo especificamos por claridad
     */
    protected $table = 'barrios';
    
    /**
     * Llave primaria
     */
    protected $primaryKey = 'id_barrio';
    
    /**
     * No usamos timestamps
     */
    public $timestamps = false;
    
    /**
     * Atributos que se pueden llenar masivamente
     */
    protected $fillable = [
        'nombre',
        'distrito',
        'latitud_centro',
        'longitud_centro',
        'activo'
    ];
    
    /**
     * Casting de atributos (convierte tipos automáticamente)
     */
    protected $casts = [
        'activo' => 'boolean',
        'latitud_centro' => 'decimal:7',
        'longitud_centro' => 'decimal:7'
    ];
    
    /**
     * Relación: Un barrio tiene muchos usuarios
     * (los vecinos que viven en este barrio)
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'id_barrio');
    }
    
    /**
     * Relación: Un barrio tiene muchas alertas predictivas
     */
    public function alertasPredictivas(): HasMany
    {
        return $this->hasMany(AlertaPredictiva::class, 'id_barrio');
    }
    
    /**
     * Relación: Un barrio tiene muchas suscripciones
     * (vecinos suscritos a alertas de este barrio)
     */
    public function suscripciones(): HasMany
    {
        return $this->hasMany(SuscripcionBarrio::class, 'id_barrio');
    }
}