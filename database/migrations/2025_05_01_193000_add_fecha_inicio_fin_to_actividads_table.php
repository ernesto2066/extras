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
        // First check if the columns don't already exist
        if (!Schema::hasColumn('actividads', 'fecha_inicio') && 
            !Schema::hasColumn('actividads', 'fecha_fin')) {
            
            // Step 1: Add the new columns as nullable
            Schema::table('actividads', function (Blueprint $table) {
                $table->date('fecha_inicio')->nullable()->after('cliente');
                $table->date('fecha_fin')->nullable()->after('hora_inicio');
            });
            
            // Step 2: Populate the new columns with data from fecha_ejecucion if it exists
            if (Schema::hasColumn('actividads', 'fecha_ejecucion')) {
                DB::statement('UPDATE actividads SET fecha_inicio = fecha_ejecucion, fecha_fin = fecha_ejecucion');
            }
            
            // Step 3: Make the columns required
            Schema::table('actividads', function (Blueprint $table) {
                $table->date('fecha_inicio')->nullable(false)->change();
                $table->date('fecha_fin')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actividads', function (Blueprint $table) {
            if (Schema::hasColumn('actividads', 'fecha_inicio')) {
                $table->dropColumn('fecha_inicio');
            }
            if (Schema::hasColumn('actividads', 'fecha_fin')) {
                $table->dropColumn('fecha_fin');
            }
        });
    }
};
