<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imagenes_incidente', function (Blueprint $table) {
            $table->id('id_imagen');
            $table->unsignedInteger('id_incidente');
            $table->string('ruta_imagen', 500);
            $table->string('nombre_original', 255);
            $table->integer('tamano_bytes')->nullable();
            $table->string('mime_type', 100);
            $table->boolean('es_principal')->default(false);
            $table->timestamp('fecha_subida')->useCurrent();
            
            // Primero crear el índice, luego la foreign key (más seguro)
            $table->index('id_incidente');
            $table->foreign('id_incidente')
                  ->references('id_incidente')
                  ->on('incidentes')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imagenes_incidente');
    }
};