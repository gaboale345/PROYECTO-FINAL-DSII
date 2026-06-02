<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuscripcionBarrio extends Model
{
    protected $table = 'suscripciones_barrio';

    protected $primaryKey = 'id_suscripcion';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_barrio',
        'activo',
        'fecha_suscripcion',
        'fecha_cancelacion',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_suscripcion' => 'datetime',
        'fecha_cancelacion' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function barrio(): BelongsTo
    {
        return $this->belongsTo(Barrio::class, 'id_barrio');
    }
}
