<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoDelito extends Model
{
    /**
     * Tabla asociada
     */
    protected $table = 'tipos_delito';
    
    /**
     * Llave primaria
     */
    protected $primaryKey = 'id_tipo_delito';
    
    /**
     * No usamos timestamps
     */
    public $timestamps = false;
    
    /**
     * Atributos que se pueden llenar masivamente
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'nivel_gravedad'
    ];
    
    /**
     * Casting de atributos
     */
    protected $casts = [
        'nivel_gravedad' => 'integer'
    ];
    
    /**
     * Relación: Un tipo de delito tiene muchos incidentes
     */
    public function incidentes(): HasMany
    {
        return $this->hasMany(Incidente::class, 'id_tipo_delito');
    }
    
    /**
     * Relación: Un tipo de delito tiene muchas alertas predictivas
     */
    public function alertasPredictivas(): HasMany
    {
        return $this->hasMany(AlertaPredictiva::class, 'id_tipo_delito');
    }
}