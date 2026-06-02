<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestigoIncidente extends Model
{
    protected $table = 'testigos_incidente';
    protected $primaryKey = 'id_testigo';
    public $timestamps = false;
    
    protected $fillable = [
        'id_incidente',
        'id_usuario_testigo',
        'comentario',
        'fecha_testimonio'
    ];
    
    public function incidente(): BelongsTo
    {
        return $this->belongsTo(Incidente::class, 'id_incidente');
    }
    
    public function usuarioTestigo(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_testigo');
    }
}