<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAuditoria extends Model
{
    protected $table = 'logs_auditoria';

    protected $primaryKey = 'id_log';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'accion',
        'tabla_afectada',
        'registro_id',
        'ip_origen',
        'user_agent',
        'detalles',
        'fecha_hora',
    ];

    protected $casts = [
        'detalles' => 'array',
        'fecha_hora' => 'datetime',
    ];
}
