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
        // Modificar la tabla docentes
        Schema::table('docentes', function (Blueprint $table) {
            $table->jsonb('disponibilidad_horaria')->nullable()->after('especialidad');
            $table->text('formacion_academica')->nullable()->after('disponibilidad_horaria');
        });

        // Crear la tabla pivote docente_materia
        Schema::create('docente_materia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->constrained('docentes')->onDelete('cascade');
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['docente_id', 'materia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docente_materia');

        Schema::table('docentes', function (Blueprint $table) {
            $table->dropColumn(['disponibilidad_horaria', 'formacion_academica']);
        });
    }
};
