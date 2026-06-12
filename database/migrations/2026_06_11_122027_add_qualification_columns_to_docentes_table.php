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
        Schema::table('docentes', function (Blueprint $table) {
            $table->boolean('profesional_area')->default(true);
            $table->boolean('tiene_maestria')->default(true);
            $table->boolean('tiene_diplomado')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            $table->dropColumn(['profesional_area', 'tiene_maestria', 'tiene_diplomado']);
        });
    }
};
