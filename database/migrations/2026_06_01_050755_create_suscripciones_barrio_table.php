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
        Schema::create('suscripciones_barrio', function (Blueprint $table) {
            $table->increments('id_suscripcion');
            $table->unsignedInteger('id_usuario');
            $table->unsignedMediumInteger('id_barrio');
            $table->boolean('activo')->nullable()->default(true);
            $table->timestamp('fecha_suscripcion')->nullable()->useCurrent();
            $table->timestamp('fecha_cancelacion')->nullable();

            $table->index(['id_barrio', 'activo'], 'idx_barrio_activo');
            $table->unique(['id_usuario', 'id_barrio', 'activo'], 'uk_usuario_barrio_activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suscripciones_barrio');
    }
};
