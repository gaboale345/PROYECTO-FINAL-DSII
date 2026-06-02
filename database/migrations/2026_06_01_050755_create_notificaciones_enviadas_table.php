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
        Schema::create('notificaciones_enviadas', function (Blueprint $table) {
            $table->increments('id_notificacion');
            $table->unsignedInteger('id_usuario');
            $table->unsignedInteger('id_alerta')->nullable()->index('id_alerta');
            $table->unsignedTinyInteger('id_canal');
            $table->string('titulo', 100);
            $table->text('mensaje');
            $table->boolean('leida')->nullable()->default(false);
            $table->timestamp('fecha_hora_envio')->nullable()->useCurrent();
            $table->timestamp('fecha_hora_leida')->nullable();
            $table->text('error_envio')->nullable();

            $table->index(['id_canal', 'fecha_hora_envio'], 'idx_canal_fecha');
            $table->index(['id_usuario', 'leida', 'fecha_hora_envio'], 'idx_usuario_leida_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones_enviadas');
    }
};
