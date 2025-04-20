<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ingeniero extends Model
{
    protected $fillable = ['nombre'];

    /**
     * Get the torre that the ingeniero belongs to.
     */
    public function torre(): BelongsTo
    {
        return $this->belongsTo(Torre::class);
    }

    /**
     * Get the jefe inmediato that the ingeniero belongs to.
     */
    public function jefeInmediato(): BelongsTo
    {
        return $this->belongsTo(JefeInmediato::class);
    }
}
