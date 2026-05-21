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
        Schema::create('cupos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_id')->constrained('carreras')->onDelete('restrict');
            $table->foreignId('gestion_id')->constrained('gestiones')->onDelete('restrict');
            $table->integer('cantidad_primera_opcion');
            $table->integer('cantidad_segunda_opcion');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['carrera_id', 'gestion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cupos');
    }
};
