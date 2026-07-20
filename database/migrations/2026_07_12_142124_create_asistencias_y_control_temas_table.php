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
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postulante_id')->constrained('postulantes')->onDelete('cascade');
            $table->foreignId('grupo_id')->constrained('grupos')->onDelete('cascade');
            $table->date('fecha');
            $table->enum('estado', ['presente', 'falta', 'licencia']);
            $table->timestamps();

            // Evitar duplicados de asistencia para el mismo estudiante, grupo y día
            $table->unique(['postulante_id', 'grupo_id', 'fecha']);
        });

        Schema::create('control_temas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained('grupos')->onDelete('cascade');
            $table->date('fecha');
            $table->string('tema');
            $table->text('descripcion')->nullable();
            $table->timestamps();

            // Un tema por grupo y día
            $table->unique(['grupo_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_temas');
        Schema::dropIfExists('asistencias');
    }
};
