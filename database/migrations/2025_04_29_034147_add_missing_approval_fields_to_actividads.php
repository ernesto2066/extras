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
            // Check if columns don't exist before trying to add them
            if (!Schema::hasColumn('actividads', 'email_notificacion')) {
                $table->string('email_notificacion')->nullable()->after('nombre_completo');
            }
            
            if (!Schema::hasColumn('actividads', 'estado')) {
                $table->enum('estado', ['pendiente', 'aprobada_coordinador', 'rechazada_coordinador', 'aprobada_final', 'rechazada_final'])
                    ->default('pendiente')
                    ->after('hora_fin');
            }
            
            if (!Schema::hasColumn('actividads', 'comentarios')) {
                $table->text('comentarios')->nullable()->after('estado');
            }
            
            if (!Schema::hasColumn('actividads', 'aprobador_id')) {
                $table->unsignedBigInteger('aprobador_id')->nullable()->after('comentarios');
            }
            
            if (!Schema::hasColumn('actividads', 'fecha_aprobacion')) {
                $table->timestamp('fecha_aprobacion')->nullable()->after('aprobador_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actividads', function (Blueprint $table) {
            //
            $table->dropColumn(['email_notificacion', 'estado', 'comentarios', 'aprobador_id', 'fecha_aprobacion']);
        });
    }
};
