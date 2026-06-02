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
        Schema::create('incidentes', function (Blueprint $table) {
            $table->increments('id_incidente');
            $table->unsignedInteger('id_usuario_reportante')->index('id_usuario_reportante');
            $table->unsignedTinyInteger('id_tipo_delito');
            $table->text('descripcion')->nullable();
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->timestamp('fecha_hora')->nullable()->useCurrent();
            $table->boolean('validado')->nullable()->default(false);
            $table->unsignedInteger('id_usuario_validador')->nullable()->index('id_usuario_validador');
            $table->timestamp('fecha_validacion')->nullable();
            $table->boolean('es_falso_reporte')->nullable()->default(false);
            $table->unsignedInteger('id_incidente_principal')->nullable()->index('id_incidente_principal');
            $table->string('estado', 20)->nullable()->default('pendiente');
            $table->boolean('es_anonimo')->nullable()->default(false);
            $table->boolean('es_por_voz')->nullable()->default(false);
            $table->boolean('es_whatsapp')->nullable()->default(false);
            $table->string('ip_reportante', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();

            $table->index(['fecha_hora', 'estado'], 'idx_incidentes_fecha_estado');
            $table->index(['id_tipo_delito', 'fecha_hora'], 'idx_tipo_fecha');
            $table->index(['latitud', 'longitud'], 'idx_ubicacion');
            $table->index(['validado', 'es_falso_reporte'], 'idx_validado_falso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidentes');
    }
};
