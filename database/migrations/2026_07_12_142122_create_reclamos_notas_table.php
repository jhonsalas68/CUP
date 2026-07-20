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
        Schema::create('reclamos_notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postulante_id')->constrained('postulantes')->onDelete('cascade');
            $table->foreignId('examen_id')->constrained('examenes')->onDelete('cascade');
            $table->text('descripcion');
            $table->string('archivo_adjunto')->nullable();
            $table->enum('estado', ['pendiente', 'en_revision', 'aceptado', 'rechazado'])->default('pendiente');
            $table->text('respuesta_docente')->nullable();
            $table->foreignId('docente_id')->nullable()->constrained('docentes')->onDelete('set null');
            $table->decimal('nota_anterior', 5, 2)->nullable();
            $table->decimal('nota_nueva', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reclamos_notas');
    }
};
