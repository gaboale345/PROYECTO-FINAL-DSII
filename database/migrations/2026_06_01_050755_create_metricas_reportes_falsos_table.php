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
        Schema::create('metricas_reportes_falsos', function (Blueprint $table) {
            $table->increments('id_metrica');
            $table->unsignedInteger('id_usuario');
            $table->unsignedSmallInteger('anio');
            $table->unsignedTinyInteger('mes');
            $table->unsignedTinyInteger('cantidad_falsos')->default(0);
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();

            $table->index(['anio', 'mes', 'cantidad_falsos'], 'idx_anio_mes_cantidad');
            $table->unique(['id_usuario', 'anio', 'mes'], 'uk_usuario_anio_mes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metricas_reportes_falsos');
    }
};
