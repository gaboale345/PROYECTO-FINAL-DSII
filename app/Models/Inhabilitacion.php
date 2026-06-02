<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inhabilitacion extends Model
{
    protected $table = 'inhabilitaciones';

    protected $primaryKey = 'id_inhabilitacion';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'fecha_inicio',
        'fecha_fin',
        'motivo',
        'cantidad_reportes_falsos',
        'id_usuario_creador',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function scopeActivas($query)
    {
        return $query->where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now());
    }
}
