<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add the new columns as nullable
        Schema::table('actividads', function (Blueprint $table) {
            $table->date('fecha_inicio')->after('cliente')->nullable();
            $table->date('fecha_fin')->after('hora_inicio')->nullable();
        });
        
        // Step 2: Populate the new columns with data from fecha_ejecucion
        DB::statement('UPDATE actividads SET fecha_inicio = fecha_ejecucion, fecha_fin = fecha_ejecucion');
        
        // Step 3: Make the columns required
        Schema::table('actividads', function (Blueprint $table) {
            $table->date('fecha_inicio')->nullable(false)->change();
            $table->date('fecha_fin')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actividads', function (Blueprint $table) {
            $table->dropColumn(['fecha_inicio', 'fecha_fin']);
        });
    }
};
