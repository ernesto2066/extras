<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $table = 'actividads';
    
    protected $fillable = [
        'documento_identidad',
        'nombre_completo',
        'email_notificacion',
        'torre_id',
        'tipo_caso_id',
        'numero_casos',
        'descripcion',
        'cliente',
        'jefe_inmediato_id',
        'fecha_ejecucion',
        'hora_inicio',
        'hora_fin',
        'estado',
        'comentarios',
        'aprobador_id',
        'fecha_aprobacion',
    ];
    
    // Estados de aprobación posibles
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_APROBADA_COORDINADOR = 'aprobada_coordinador';
    const ESTADO_RECHAZADA_COORDINADOR = 'rechazada_coordinador';
    const ESTADO_APROBADA_FINAL = 'aprobada_final';
    const ESTADO_RECHAZADA_FINAL = 'rechazada_final';
    
    // Colores para los estados
    public static function getEstadoColor(string $estado): string
    {
        return match($estado) {
            self::ESTADO_PENDIENTE => 'warning',
            self::ESTADO_APROBADA_COORDINADOR => 'info',
            self::ESTADO_RECHAZADA_COORDINADOR => 'danger',
            self::ESTADO_APROBADA_FINAL => 'success',
            self::ESTADO_RECHAZADA_FINAL => 'danger',
            default => 'secondary',
        };
    }

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
    
    // Relación para el aprobador (usuario que aprueba/rechaza)
    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobador_id');
    }
    
    // Determina si la actividad puede ser aprobada por un coordinador
    public function puedeSerAprobadaPorCoordinador(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }
    
    // Determina si la actividad puede ser aprobada por un administrador
    public function puedeSerAprobadaPorAdministrador(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE || 
               $this->estado === self::ESTADO_APROBADA_COORDINADOR;
    }
}
