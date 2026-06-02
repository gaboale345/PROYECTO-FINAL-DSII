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
        Schema::create('alertas_predictivas', function (Blueprint $table) {
            $table->increments('id_alerta');
            $table->unsignedMediumInteger('id_barrio');
            $table->unsignedTinyInteger('id_tipo_delito')->index('id_tipo_delito');
            $table->string('franja_horaria', 20);
            $table->string('nivel_riesgo', 20)->nullable();
            $table->decimal('probabilidad', 5)->nullable()->index('idx_probabilidad_desc');
            $table->timestamp('fecha_generacion')->nullable()->useCurrent();
            $table->timestamp('fecha_expiracion')->nullable();
            $table->boolean('activa')->nullable()->default(true);

            $table->index(['id_barrio', 'activa', 'nivel_riesgo'], 'idx_barrio_activa_riesgo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertas_predictivas');
    }
};
