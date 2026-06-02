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
        Schema::create('tipos_delito', function (Blueprint $table) {
            $table->tinyIncrements('id_tipo_delito');
            $table->string('nombre', 50)->unique('nombre');
            $table->text('descripcion')->nullable();
            $table->tinyInteger('nivel_gravedad')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_delito');
    }
};
