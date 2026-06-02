<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImagenIncidente extends Model
{
    protected $table = 'imagenes_incidente';
    protected $primaryKey = 'id_imagen';
    public $timestamps = false;
    
    protected $fillable = [
        'id_incidente',
        'ruta_imagen',
        'nombre_original',
        'tamano_bytes',
        'mime_type',
        'es_principal'
    ];
    
    protected $casts = [
        'es_principal' => 'boolean',
        'fecha_subida' => 'datetime'
    ];
    
    public function incidente(): BelongsTo
    {
        return $this->belongsTo(Incidente::class, 'id_incidente', 'id_incidente');
    }
    
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->ruta_imagen);
    }
}