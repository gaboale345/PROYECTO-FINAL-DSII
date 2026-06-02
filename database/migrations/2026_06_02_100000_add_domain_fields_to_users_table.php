<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('id_usuario')->nullable()->unique()->after('id');
            $table->unsignedTinyInteger('id_rol')->default(1)->after('password');
            $table->unsignedMediumInteger('id_barrio')->nullable()->after('id_rol');
            $table->text('telefono')->nullable()->after('id_barrio');
            $table->boolean('bloqueado')->default(false)->after('telefono');
            $table->timestamp('bloqueado_hasta')->nullable()->after('bloqueado');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['id_usuario', 'id_rol', 'id_barrio', 'telefono', 'bloqueado', 'bloqueado_hasta']);
        });
    }
};
