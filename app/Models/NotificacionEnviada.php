<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionEnviada extends Model
{
    protected $table = 'notificaciones_enviadas';

    protected $primaryKey = 'id_notificacion';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_alerta',
        'id_canal',
        'titulo',
        'mensaje',
        'leida',
        'fecha_hora_envio',
        'fecha_hora_leida',
        'error_envio',
    ];

    protected $casts = [
        'leida' => 'boolean',
        'fecha_hora_envio' => 'datetime',
        'fecha_hora_leida' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}
