<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inhabilitaciones', function (Blueprint $table) {
            $table->increments('id_inhabilitacion');
            $table->unsignedInteger('id_usuario');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->text('motivo');
            $table->unsignedTinyInteger('cantidad_reportes_falsos')->default(1);
            $table->unsignedInteger('id_usuario_creador')->nullable()->index('id_usuario_creador');

            $table->index(['fecha_inicio', 'fecha_fin'], 'idx_fechas_activas');
            $table->index(['id_usuario', 'fecha_inicio', 'fecha_fin'], 'idx_usuario_fechas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inhabilitaciones');
    }
};
