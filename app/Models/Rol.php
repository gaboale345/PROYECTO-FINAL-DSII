<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    /**
     * Tabla asociada al modelo
     * Laravel asume que la tabla se llama 'rols' (plural)
     * pero nuestra tabla se llama 'roles', así que la especificamos
     */
    protected $table = 'roles';
    
    /**
     * Llave primaria de la tabla
     * Laravel asume que se llama 'id', pero la nuestra es 'id_rol'
     */
    protected $primaryKey = 'id_rol';
    
    /**
     * Indica si el modelo usa timestamps (created_at, updated_at)
     * Nuestra tabla NO tiene esos campos, así que lo desactivamos
     */
    public $timestamps = false;
    
    /**
     * Atributos que pueden ser asignados masivamente
     * (protección contra asignación masiva)
     */
    protected $fillable = [
        'nombre_rol',
        'descripcion'
    ];
    
    /**
     * Relación: Un rol tiene muchos usuarios
     * Un rol (ej: Vecino) puede tener muchos usuarios
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'id_rol');
    }
}