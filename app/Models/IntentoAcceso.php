<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntentoAcceso extends Model
{
    protected $table = 'intentos_acceso';

    protected $primaryKey = 'id_intento';

    public $timestamps = false;

    protected $fillable = [
        'email',
        'ip_origen',
        'exitoso',
        'fecha_hora',
    ];

    protected $casts = [
        'exitoso' => 'boolean',
        'fecha_hora' => 'datetime',
    ];
}
