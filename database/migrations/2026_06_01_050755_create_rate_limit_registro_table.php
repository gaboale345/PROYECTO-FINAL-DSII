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
        Schema::create('rate_limit_registro', function (Blueprint $table) {
            $table->increments('id_registro');
            $table->unsignedInteger('id_usuario')->nullable()->index('id_usuario');
            $table->string('ip_origen', 45);
            $table->string('endpoint', 100)->nullable();
            $table->unsignedTinyInteger('peticiones_en_ventana')->nullable();
            $table->unsignedTinyInteger('ventana_segundos')->nullable();
            $table->boolean('fue_bloqueado')->nullable()->default(false);
            $table->timestamp('fecha_hora')->nullable()->useCurrent();

            $table->index(['fue_bloqueado', 'fecha_hora'], 'idx_bloqueado_fecha');
            $table->index(['ip_origen', 'ventana_segundos', 'fecha_hora'], 'idx_ip_ventana');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_limit_registro');
    }
};
