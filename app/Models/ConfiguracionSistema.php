<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ConfiguracionSistema extends Model
{
    protected $table = 'configuracion_sistema';

    protected $primaryKey = 'id_config';

    public $timestamps = false;

    const UPDATED_AT = 'updated_at';

    protected $fillable = ['clave', 'valor', 'descripcion', 'id_usuario_actualizo'];

    public static function obtener(string $clave, mixed $default = null): mixed
    {
        return Cache::remember("config.{$clave}", 300, function () use ($clave, $default) {
            $valor = static::where('clave', $clave)->value('valor');

            return $valor !== null ? $valor : $default;
        });
    }

    public static function establecer(string $clave, string $valor, ?string $descripcion = null): void
    {
        static::updateOrCreate(
            ['clave' => $clave],
            ['valor' => $valor, 'descripcion' => $descripcion]
        );

        Cache::forget("config.{$clave}");
    }
}
