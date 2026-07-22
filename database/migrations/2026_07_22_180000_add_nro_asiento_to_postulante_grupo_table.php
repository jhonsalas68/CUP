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
        Schema::table('postulante_grupo', function (Blueprint $table) {
            $table->integer('nro_asiento')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('postulante_grupo', function (Blueprint $table) {
            $table->dropColumn('nro_asiento');
        });
    }
};
