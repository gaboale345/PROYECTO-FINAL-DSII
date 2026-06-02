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
        Schema::create('testigos_incidente', function (Blueprint $table) {
            $table->increments('id_testigo');
            $table->unsignedInteger('id_incidente');
            $table->unsignedInteger('id_usuario_testigo');
            $table->text('comentario')->nullable();
            $table->timestamp('fecha_testimonio')->nullable()->useCurrent();

            $table->index(['id_usuario_testigo', 'fecha_testimonio'], 'idx_testigo_fecha');
            $table->unique(['id_incidente', 'id_usuario_testigo'], 'uk_incidente_testigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testigos_incidente');
    }
};
