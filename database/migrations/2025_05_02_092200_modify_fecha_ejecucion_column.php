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
        Schema::table('actividads', function (Blueprint $table) {
            // Eliminamos la columna fecha_ejecucion ya que ha sido reemplazada por fecha_inicio y fecha_fin
            $table->dropColumn('fecha_ejecucion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actividads', function (Blueprint $table) {
            // Si necesitamos revertir, recreamos la columna
            $table->date('fecha_ejecucion')->after('jefe_inmediato_id');
        });
    }
};
