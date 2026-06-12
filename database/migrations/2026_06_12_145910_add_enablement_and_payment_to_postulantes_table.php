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
            $table->boolean('habilitado')->default(false)->after('libreta_legalizada');
            $table->string('mensaje_documentos', 255)->nullable()->after('habilitado');
            $table->boolean('pago_realizado')->default(false)->after('mensaje_documentos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            $table->dropColumn(['habilitado', 'mensaje_documentos', 'pago_realizado']);
        });
    }
};
