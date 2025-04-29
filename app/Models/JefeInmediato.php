<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class JefeInmediato extends Model implements Auditable
{
    use AuditableTrait;
    
    protected $fillable = ['nombre'];

    // Relación con Actividad
    public function actividades()
    {
        return $this->hasMany(Actividad::class);
    }
}
