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
        Schema::table('jefe_inmediatos', function (Blueprint $table) {
            // Primero eliminar la clave foránea
            $table->dropForeign(['ingeniero_id']);
            // Luego eliminar la columna
            $table->dropColumn('ingeniero_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jefe_inmediatos', function (Blueprint $table) {
            // Recrear la columna y la clave foránea en caso de rollback
            $table->foreignId('ingeniero_id')->constrained('ingenieros')->onDelete('cascade');
        });
    }
};
