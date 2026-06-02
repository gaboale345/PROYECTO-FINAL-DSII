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
        Schema::create('barrios', function (Blueprint $table) {
            $table->mediumIncrements('id_barrio');
            $table->string('nombre', 100);
            $table->string('distrito', 50);
            $table->decimal('latitud_centro', 10, 7)->nullable();
            $table->decimal('longitud_centro', 10, 7)->nullable();
            $table->boolean('activo')->nullable()->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barrios');
    }
};
