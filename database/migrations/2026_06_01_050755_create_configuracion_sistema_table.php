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
        Schema::create('configuracion_sistema', function (Blueprint $table) {
            $table->tinyIncrements('id_config');
            $table->string('clave', 50)->unique('clave');
            $table->text('valor');
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('id_usuario_actualizo')->nullable()->index('id_usuario_actualizo');
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_sistema');
    }
};
