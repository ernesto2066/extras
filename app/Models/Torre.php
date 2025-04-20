<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Torre extends Model
{
    protected $fillable = ['nombre'];

    // Relación con Actividad
    public function actividades()
    {
        return $this->hasMany(Actividad::class);
    }
}
