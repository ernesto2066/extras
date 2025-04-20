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
        Schema::create('actividads', function (Blueprint $table) {
            $table->id();
            $table->string('documento_identidad', 20);
            $table->string('nombre_completo');
            $table->foreignId('jefe_inmediato_id')->constrained('jefe_inmediatos');
            $table->foreignId('torre_id')->constrained('torres');
            $table->foreignId('tipo_caso_id')->constrained('tipo_casos');
            $table->string('numero_casos');
            $table->text('descripcion');
            $table->string('cliente');
            $table->date('fecha_ejecucion');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividads');
    }
};
