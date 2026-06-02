<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaPredictiva extends Model
{
    protected $table = 'alertas_predictivas';

    protected $primaryKey = 'id_alerta';

    public $timestamps = false;

    protected $fillable = [
        'id_barrio',
        'id_tipo_delito',
        'franja_horaria',
        'nivel_riesgo',
        'probabilidad',
        'fecha_generacion',
        'fecha_expiracion',
        'activa',
    ];

    protected $casts = [
        'probabilidad' => 'float',
        'activa' => 'boolean',
        'fecha_generacion' => 'datetime',
        'fecha_expiracion' => 'datetime',
    ];

    public function barrio(): BelongsTo
    {
        return $this->belongsTo(Barrio::class, 'id_barrio');
    }

    public function tipoDelito(): BelongsTo
    {
        return $this->belongsTo(TipoDelito::class, 'id_tipo_delito');
    }

    public function getMensajeAttribute(): string
    {
        $barrio = $this->barrio->nombre ?? 'tu barrio';
        $tipo = $this->tipoDelito->nombre ?? 'incidentes';

        return "Riesgo {$this->nivel_riesgo} de {$tipo} en {$barrio} ({$this->franja_horaria}) - "
            .round($this->probabilidad * 100).'% probabilidad';
    }
}
