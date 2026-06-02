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
        Schema::create('canales_notificacion', function (Blueprint $table) {
            $table->tinyIncrements('id_canal');
            $table->string('nombre_canal', 20)->unique('nombre_canal');
            $table->text('descripcion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canales_notificacion');
    }
};
