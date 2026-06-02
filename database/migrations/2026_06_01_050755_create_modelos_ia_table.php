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
        Schema::create('modelos_ia', function (Blueprint $table) {
            $table->increments('id_modelo');
            $table->string('version', 20)->unique('version');
            $table->decimal('precision_porcentaje', 5)->nullable()->index('idx_precision');
            $table->integer('dataset_filas_entrenamiento')->nullable();
            $table->integer('dataset_filas_prueba')->nullable();
            $table->timestamp('fecha_entrenamiento')->nullable()->useCurrent();
            $table->boolean('activo')->nullable()->default(false);
            $table->string('ruta_archivo')->nullable();
            $table->json('metricas_adicionales')->nullable();
            $table->json('hiperparametros')->nullable();

            $table->index(['activo', 'fecha_entrenamiento'], 'idx_activo_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modelos_ia');
    }
};
