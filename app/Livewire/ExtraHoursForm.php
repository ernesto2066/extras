<?php

namespace App\Livewire;

use App\Models\Actividad;
use App\Models\JefeInmediato;
use App\Models\TipoCaso;
use App\Models\Torre;
use App\Notifications\HoraExtraRegistrada;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Log;
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
    public $fecha_inicio;
    
    #[Rule('required|date')]
    public $fecha_fin;
    
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
        $this->fecha_inicio = date('Y-m-d');
        $this->fecha_fin = date('Y-m-d');
    }

    public $currentStep = 1;
    public $lastActivityId = null;

    public function save()
    {
        $this->validate();

        $fechaHoraInicio = Carbon::parse($this->fecha_inicio . ' ' . $this->hora_inicio);
        $fechaHoraFin = Carbon::parse($this->fecha_fin . ' ' . $this->hora_fin);
        $ahora = Carbon::now();
        
        // Validar que las fechas no sean futuras
        if ($fechaHoraInicio->gt($ahora) || $fechaHoraFin->gt($ahora)) {
            session()->flash('warning', 'No se pueden registrar horas extras para fechas futuras.');
            return;
        }
        
        if ($fechaHoraFin->lt($fechaHoraInicio)) {
            session()->flash('warning', 'La fecha y hora de fin deben ser posteriores a la fecha y hora de inicio.');
            return;
        }

        $actividad = Actividad::create([
            'documento_identidad' => $this->documento_identidad,
            'nombre_completo' => $this->nombre_completo,
            'torre_id' => $this->torre_id,
            'tipo_caso_id' => $this->tipo_caso_id,
            'numero_casos' => $this->numero_casos,
            'descripcion' => $this->descripcion,
            'cliente' => $this->cliente,
            'jefe_inmediato_id' => $this->jefe_inmediato_id,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'hora_inicio' => $this->hora_inicio,
            'hora_fin' => $this->hora_fin,
            'email_notificacion' => $this->email_notificacion,
            'estado' => Actividad::ESTADO_PENDIENTE,
        ]);

        // Store the ID of the newly created activity
        $this->lastActivityId = $actividad->id;
        
        // Enviar notificación por correo electrónico
        if ($this->email_notificacion) {
            try {
                $notifiable = new AnonymousNotifiable;
                $notifiable->route('mail', $this->email_notificacion);
                $notifiable->notify(new HoraExtraRegistrada($actividad));
                
                Log::info('Notificación enviada a: ' . $this->email_notificacion);
            } catch (\Exception $e) {
                Log::error('Error al enviar la notificación: ' . $e->getMessage());
            }
        }
        
        session()->flash('message', 'Hora extra con el consecutivo #' . $this->lastActivityId . ' registrada con éxito en la base de datos.');

        // Reset form fields
        $this->reset([
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
