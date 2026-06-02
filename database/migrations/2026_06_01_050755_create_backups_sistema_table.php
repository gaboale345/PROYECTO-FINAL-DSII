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
        Schema::create('backups_sistema', function (Blueprint $table) {
            $table->increments('id_backup');
            $table->string('tipo_backup', 20);
            $table->timestamp('fecha_inicio')->nullable()->useCurrent();
            $table->timestamp('fecha_fin')->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->string('ruta_archivo');
            $table->string('estado', 20)->nullable()->default('en_proceso');
            $table->text('detalles')->nullable();

            $table->index(['fecha_inicio', 'estado'], 'idx_backups_fecha_estado');
            $table->index(['tipo_backup', 'estado'], 'idx_tipo_estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backups_sistema');
    }
};
