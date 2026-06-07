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
        Schema::create('postulantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('ci')->unique();
            $table->string('telefono')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->foreignId('carrera_primera_opcion_id')->constrained('carreras')->onDelete('restrict');
            $table->foreignId('carrera_segunda_opcion_id')->nullable()->constrained('carreras')->onDelete('restrict');
            $table->foreignId('gestion_id')->constrained('gestiones')->onDelete('restrict');
            $table->enum('estado_admision', [
                'pendiente',
                'admitido_primera_opcion',
                'admitido_segunda_opcion',
                'no_admitido',
                'reprobado'
            ])->default('pendiente');
            $table->decimal('nota_final', 5, 2)->nullable();
            $table->boolean('ci_vigente')->default(false);
            $table->boolean('titulo_bachiller')->default(false);
            $table->boolean('libreta_legalizada')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado_admision');
            $table->index('nota_final');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postulantes');
    }
};
