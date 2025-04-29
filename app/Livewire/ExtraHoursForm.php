<?php

namespace App\Livewire;

use App\Models\JefeInmediato;
use App\Models\TipoCaso;
use App\Models\Torre;
use App\Models\Actividad;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Carbon\Carbon;

class ExtraHoursForm extends Component
{
    #[Rule('required|exists:torres,id')]
    public $torre_id;
    
    #[Rule('required|exists:tipo_casos,id')]
    public $tipo_caso_id;
    
    #[Rule('required|string')]
    public $numero_casos;
    
    #[Rule('required|string')]
    public $descripcion;
    
    #[Rule('required|string|max:255')]
    public $cliente;
    
    #[Rule('required|exists:jefe_inmediatos,id')]
    public $jefe_inmediato_id;
    
    #[Rule('required|date')]
    public $fecha_ejecucion;
    
    #[Rule('required|date_format:H:i')]
    public $hora_inicio;
    
    #[Rule('required|date_format:H:i')]
    public $hora_fin;

    #[Rule('required|string|max:20')]
    public $documento_identidad = '';
    
    #[Rule('required|email|max:255')]
    public $email_notificacion = '';

    #[Rule('required|string|max:255')]
    public $nombre_completo = '';

    public function mount()
    {
        $this->fecha_ejecucion = date('Y-m-d');
    }

    public $currentStep = 1;
    public $lastActivityId = null;

    public function save()
    {
        $this->validate();

        if (Carbon::parse($this->hora_fin)->lte(Carbon::parse($this->hora_inicio))) {
            session()->flash('warning', 'La hora de fin debe ser posterior a la hora de inicio.');
            return;
        }

        $actividad = Actividad::create([
            'documento_identidad' => $this->documento_identidad,
            'nombre_completo' => $this->nombre_completo,
            'email_notificacion' => $this->email_notificacion,
            'torre_id' => $this->torre_id,
            'tipo_caso_id' => $this->tipo_caso_id,
            'numero_casos' => $this->numero_casos,
            'descripcion' => $this->descripcion,
            'cliente' => $this->cliente,
            'jefe_inmediato_id' => $this->jefe_inmediato_id,
            'fecha_ejecucion' => $this->fecha_ejecucion,
            'hora_inicio' => $this->hora_inicio,
            'hora_fin' => $this->hora_fin,
            'estado' => Actividad::ESTADO_PENDIENTE,
        ]);

        // Store the ID of the newly created activity
        $this->lastActivityId = $actividad->id;
        
        session()->flash('message', 'Hora extra con el consecutivo #' . $this->lastActivityId . ' registrada con éxito en la base de datos.');

        // Reset form fields
        $this->reset([
            'torre_id', 
            'tipo_caso_id',
            'numero_casos', 
            'descripcion',  
            'cliente',
            'jefe_inmediato_id',
            'fecha_ejecucion',
            'hora_inicio',
            'hora_fin',
        ]);
        
        // Do not reset personal information to make it easier to register multiple activities
        // $this->reset(['documento_identidad', 'nombre_completo']);
        
        // Set the step back to 1 (personal information)
        $this->currentStep = 1;
        
        // Emit an event to update Alpine.js state
        $this->dispatch('resetToStep1');
    }

    public function render()
    {
        return view('livewire.extra-hours-form', [
            'torres' => Torre::orderBy('nombre')->get(),
            'jefesInmediatos' => JefeInmediato::orderBy('nombre')->get(),
            'tiposCaso' => TipoCaso::orderBy('descripcion')->get(),
        ]);
    }
}
