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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id_usuario');
            $table->string('nombre_completo', 100);
            $table->string('email', 150)->unique('email');
            $table->string('telefono', 20)->nullable();
            $table->text('hash_password');
            $table->unsignedTinyInteger('id_rol');
            $table->unsignedMediumInteger('id_barrio')->nullable()->index('id_barrio');
            $table->boolean('activo')->nullable()->default(true);
            $table->boolean('bloqueado')->nullable()->default(false);
            $table->timestamp('fecha_registro')->nullable()->useCurrent();
            $table->timestamp('ultimo_acceso')->nullable();
            $table->string('token_recuperacion', 100)->nullable();
            $table->timestamp('token_expiracion')->nullable();

            $table->index(['email', 'activo'], 'idx_email_activo');
            $table->index(['id_rol', 'bloqueado'], 'idx_rol_bloqueado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
