<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $table = 'actividads';
    
    protected $fillable = [
        'documento_identidad',
        'nombre_completo',
        'torre_id',
        'tipo_caso_id',
        'numero_casos',
        'descripcion',
        'cliente',
        'jefe_inmediato_id',
        'fecha_ejecucion',
        'hora_inicio',
        'hora_fin',
    ];

    // Relaciones
    public function jefeInmediato()
    {
        return $this->belongsTo(JefeInmediato::class);
    }

    public function torre()
    {
        return $this->belongsTo(Torre::class);
    }

    public function tipoCaso()
    {
        return $this->belongsTo(TipoCaso::class);
    }
}
