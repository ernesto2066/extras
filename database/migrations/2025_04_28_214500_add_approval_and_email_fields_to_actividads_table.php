<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function run(): void
    {
        Schema::table('actividads', function (Blueprint $table) {
            $table->string('email_notificacion')->nullable()->after('nombre_completo');
            $table->enum('estado', ['pendiente', 'aprobada_coordinador', 'rechazada_coordinador', 'aprobada_final', 'rechazada_final'])
                ->default('pendiente')
                ->after('hora_fin');
            $table->text('comentarios')->nullable()->after('estado');
            $table->unsignedBigInteger('aprobador_id')->nullable()->after('comentarios');
            $table->timestamp('fecha_aprobacion')->nullable()->after('aprobador_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actividads', function (Blueprint $table) {
            $table->dropColumn(['email_notificacion', 'estado', 'comentarios', 'aprobador_id', 'fecha_aprobacion']);
        });
    }
};
