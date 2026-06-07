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
        Schema::table('postulantes', function (Blueprint $table) {
            $table->string('sexo', 20)->nullable()->after('fecha_nacimiento');
            $table->string('direccion', 255)->nullable()->after('sexo');
            $table->string('colegio_procedencia', 255)->nullable()->after('telefono');
            $table->string('ciudad', 100)->nullable()->after('colegio_procedencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            $table->dropColumn(['sexo', 'direccion', 'colegio_procedencia', 'ciudad']);
        });
    }
};
