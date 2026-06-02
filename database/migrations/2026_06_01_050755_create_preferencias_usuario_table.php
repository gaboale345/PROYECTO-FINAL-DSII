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
        Schema::create('preferencias_usuario', function (Blueprint $table) {
            $table->increments('id_preferencia');
            $table->unsignedInteger('id_usuario');
            $table->unsignedTinyInteger('id_canal')->index('id_canal');
            $table->boolean('activo')->nullable()->default(true);

            $table->index(['id_usuario', 'activo'], 'idx_usuario_activo');
            $table->unique(['id_usuario', 'id_canal'], 'uk_usuario_canal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preferencias_usuario');
    }
};
