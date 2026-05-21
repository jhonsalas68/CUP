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
        Schema::create('examenes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('materia_id')->constrained('materias')->onDelete('restrict');
            $table->foreignId('gestion_id')->constrained('gestiones')->onDelete('restrict');
            $table->decimal('ponderacion', 5, 2);
            $table->date('fecha');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examenes');
    }
};
