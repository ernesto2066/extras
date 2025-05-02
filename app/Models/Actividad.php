<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Actividad extends Model implements Auditable
{
    use AuditableTrait;
    
    /**
     * Atributos que deben ser auditados
     */
    protected $auditInclude = [
        'estado',
        'comentarios',
        'aprobador_id',
        'fecha_aprobacion'
    ];
    
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
        'fecha_inicio',
        'fecha_fin',
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
    
    /**
     * Calcula la duración total en horas y minutos entre fecha/hora inicio y fecha/hora fin
     *
     * @return float Duración en horas (con decimales para los minutos)
     */
    public function calcularDuracionHoras(): float
    {
        $inicio = \Carbon\Carbon::parse($this->fecha_inicio . ' ' . $this->hora_inicio);
        $fin = \Carbon\Carbon::parse($this->fecha_fin . ' ' . $this->hora_fin);
        
        // Si la fecha/hora de fin es anterior a la fecha/hora de inicio, consideramos que es inválido
        if ($fin->lt($inicio)) {
            return 0;
        }
        
        // Calculamos la diferencia en minutos desde inicio hasta fin
        $diffMinutos = $inicio->diffInMinutes($fin);
        
        // Convertir a horas con 2 decimales
        return round($diffMinutos / 60, 2);
    }
    
    /**
     * Determina si las horas extras ocurren en un día festivo o fin de semana
     *
     * @return bool
     */
    public function esDiaFestivo(): bool
    {
        $fechaInicio = \Carbon\Carbon::parse($this->fecha_inicio);
        $fechaFin = \Carbon\Carbon::parse($this->fecha_fin);
        
        // Si alguna de las fechas es festivo o fin de semana, consideramos toda la actividad como festiva
        return \App\Services\FestivosColombiaService::esFestivo($fechaInicio) || 
               \App\Services\FestivosColombiaService::esFestivo($fechaFin) || 
               \App\Services\FestivosColombiaService::esFinDeSemana($fechaInicio) || 
               \App\Services\FestivosColombiaService::esFinDeSemana($fechaFin);
    }
    
    /**
     * Obtiene el tipo de hora extra (normal o festiva)
     *
     * @return string
     */
    public function getTipoHoraExtra(): string
    {
        return $this->esDiaFestivo() ? 'Festiva' : 'Normal';
    }
}
