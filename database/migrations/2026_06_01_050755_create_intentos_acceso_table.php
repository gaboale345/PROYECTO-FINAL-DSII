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
        Schema::create('intentos_acceso', function (Blueprint $table) {
            $table->increments('id_intento');
            $table->string('email', 150);
            $table->string('ip_origen', 45);
            $table->boolean('exitoso')->nullable()->default(false);
            $table->timestamp('fecha_hora')->nullable()->useCurrent();

            $table->index(['email', 'ip_origen', 'fecha_hora'], 'idx_email_ip_fecha');
            $table->index(['exitoso', 'fecha_hora'], 'idx_exitoso_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intentos_acceso');
    }
};
