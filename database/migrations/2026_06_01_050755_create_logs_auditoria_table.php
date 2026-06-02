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
        Schema::create('logs_auditoria', function (Blueprint $table) {
            $table->bigIncrements('id_log');
            $table->unsignedInteger('id_usuario')->nullable();
            $table->string('accion', 50);
            $table->string('tabla_afectada', 50)->nullable();
            $table->unsignedInteger('registro_id')->nullable();
            $table->string('ip_origen', 45);
            $table->text('user_agent')->nullable();
            $table->json('detalles')->nullable();
            $table->timestamp('fecha_hora')->nullable()->useCurrent();

            $table->index(['accion', 'fecha_hora'], 'idx_accion_fecha');
            $table->index(['ip_origen', 'fecha_hora'], 'idx_ip_fecha');
            $table->index(['tabla_afectada', 'registro_id'], 'idx_tabla_registro');
            $table->index(['id_usuario', 'fecha_hora'], 'idx_usuario_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_auditoria');
    }
};
