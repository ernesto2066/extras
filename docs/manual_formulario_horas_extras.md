# Manual Técnico del Formulario de Horas Extras - POSITIVOS

## Introducción

Este documento describe la implementación técnica del formulario público para registro de horas extras en el proyecto POSITIVOS. Este componente está construido utilizando Livewire para la funcionalidad interactiva, Alpine.js para comportamientos dinámicos y TailwindCSS para el diseño visual.

## Tecnologías Utilizadas

- **Laravel** - Framework de desarrollo backend
- **Livewire** - Framework para componentes dinámicos
- **Alpine.js** - Framework JavaScript minimalista para interactividad
- **TailwindCSS** - Framework CSS utility-first
- **Blade** - Sistema de plantillas de Laravel

## Estructura del Formulario

El formulario de horas extras implementa un flujo multi-paso (wizard) para mejorar la experiencia del usuario dividiendo el proceso en secciones lógicas.

### Esquema General

```
┌─────────────────────────────────────────┐
│                                         │
│      FORMULARIO DE HORAS EXTRAS         │
│                                         │
├─────────────┬─────────────┬─────────────┤
│             │             │             │
│  Información│  Detalles   │   Fecha y   │
│   Personal  │  del Caso   │    Horas    │
│             │             │             │
└─────────────┴─────────────┴─────────────┘
       ↓             ↓             ↓
┌─────────────┐┌─────────────┐┌─────────────┐
│ Documento   ││ Torre       ││ Fecha       │
│ Nombre      ││ Tipo de Caso││ Hora Inicio │
│ Jefe        ││ Justificación││ Hora Fin   │
└─────────────┘└─────────────┘└─────────────┘
```

## Implementación Técnica

### Componente Livewire Principal

El formulario se implementa como un componente Livewire que gestiona el estado y la navegación entre pasos.

```php
// app/Http/Livewire/FormularioHorasExtras.php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Actividad;
use App\Models\JefeInmediato;
use App\Models\Torre;
use App\Models\TipoCaso;

class FormularioHorasExtras extends Component
{
    // Propiedades del formulario
    public $currentStep = 1;
    public $totalSteps = 3;
    
    // Datos del paso 1: Información Personal
    public $documento;
    public $nombre;
    public $jefe_inmediato_id;
    public $email_notificacion;
    
    // Datos del paso 2: Detalles del Caso
    public $torre_id;
    public $tipo_caso_id;
    public $justificacion;
    
    // Datos del paso 3: Fecha y Horas
    public $fecha;
    public $hora_inicio;
    public $hora_fin;
    
    // Reglas de validación por paso
    protected $rules = [
        // Reglas para paso 1
        'documento' => 'required|string|max:20',
        'nombre' => 'required|string|max:255',
        'jefe_inmediato_id' => 'required|exists:jefe_inmediatos,id',
        'email_notificacion' => 'required|email|max:255',
        
        // Reglas para paso 2
        'torre_id' => 'required|exists:torres,id',
        'tipo_caso_id' => 'required|exists:tipo_casos,id',
        'justificacion' => 'required|string|min:10',
        
        // Reglas para paso 3
        'fecha' => 'required|date|after_or_equal:today',
        'hora_inicio' => 'required|date_format:H:i',
        'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
    ];
    
    public function render()
    {
        return view('livewire.formulario-horas-extras', [
            'jefes' => JefeInmediato::orderBy('nombre')->get(),
            'torres' => Torre::orderBy('nombre')->get(),
            'tiposcaso' => TipoCaso::orderBy('nombre')->get(),
        ]);
    }
    
    public function nextStep()
    {
        // Validar solo las reglas del paso actual
        $this->validateStep($this->currentStep);
        
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }
    
    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }
    
    public function validateStep($step)
    {
        // Extraer las reglas para el paso actual
        $stepRules = $this->getStepRules($step);
        $this->validate($stepRules);
    }
    
    protected function getStepRules($step)
    {
        // Retornar las reglas según el paso
        switch ($step) {
            case 1:
                return [
                    'documento' => $this->rules['documento'],
                    'nombre' => $this->rules['nombre'],
                    'jefe_inmediato_id' => $this->rules['jefe_inmediato_id'],
                    'email_notificacion' => $this->rules['email_notificacion'],
                ];
            case 2:
                return [
                    'torre_id' => $this->rules['torre_id'],
                    'tipo_caso_id' => $this->rules['tipo_caso_id'],
                    'justificacion' => $this->rules['justificacion'],
                ];
            case 3:
                return [
                    'fecha' => $this->rules['fecha'],
                    'hora_inicio' => $this->rules['hora_inicio'],
                    'hora_fin' => $this->rules['hora_fin'],
                ];
            default:
                return [];
        }
    }
    
    public function submitForm()
    {
        // Validar todo el formulario
        $this->validate();
        
        // Crear nueva actividad
        Actividad::create([
            'documento' => $this->documento,
            'nombre' => $this->nombre,
            'jefe_inmediato_id' => $this->jefe_inmediato_id,
            'email_notificacion' => $this->email_notificacion,
            'torre_id' => $this->torre_id,
            'tipo_caso_id' => $this->tipo_caso_id,
            'justificacion' => $this->justificacion,
            'fecha' => $this->fecha,
            'hora_inicio' => $this->hora_inicio,
            'hora_fin' => $this->hora_fin,
            'estado' => 'pendiente',
        ]);
        
        // Redireccionar o mostrar mensaje de éxito
        session()->flash('message', 'Solicitud de horas extras registrada correctamente.');
        
        // Reiniciar el formulario
        $this->reset();
        $this->currentStep = 1;
    }
}
```

### Plantilla Blade con Alpine.js

La vista Blade integra Livewire y Alpine.js para gestionar el estado del formulario y las transiciones entre pasos.

```html
<!-- resources/views/livewire/formulario-horas-extras.blade.php -->
<div>
    <div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg overflow-hidden">
        <!-- Indicador de Pasos -->
        <div class="px-4 py-5 sm:px-6 bg-green-700 text-white">
            <div class="flex justify-between">
                <h2 class="text-xl font-semibold">Registro de Horas Extras</h2>
                <div class="flex space-x-2">
                    <span class="text-sm">Paso {{ $currentStep }} de {{ $totalSteps }}</span>
                </div>
            </div>
            
            <!-- Barra de Progreso -->
            <div class="mt-4 relative pt-1">
                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-green-200">
                    <div style="width:{{ ($currentStep / $totalSteps) * 100 }}%" 
                         class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-white transition-all duration-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenedor de Pasos -->
        <div class="p-6" x-data="{ currentStep: @entangle('currentStep') }">
            <!-- Paso 1: Información Personal -->
            <div x-show="currentStep === 1" class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900">Información Personal</h3>
                
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                    <div>
                        <label for="documento" class="block text-sm font-medium text-gray-700">Documento *</label>
                        <input wire:model="documento" type="text" id="documento" 
                               class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        @error('documento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre Completo *</label>
                        <input wire:model="nombre" type="text" id="nombre" 
                               class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="sm:col-span-2">
                        <label for="jefe_inmediato_id" class="block text-sm font-medium text-gray-700">Jefe Inmediato *</label>
                        <select wire:model="jefe_inmediato_id" id="jefe_inmediato_id" 
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            <option value="">Seleccione...</option>
                            @foreach($jefes as $jefe)
                                <option value="{{ $jefe->id }}">{{ $jefe->nombre }}</option>
                            @endforeach
                        </select>
                        @error('jefe_inmediato_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="sm:col-span-2">
                        <label for="email_notificacion" class="block text-sm font-medium text-gray-700">Email para Notificaciones *</label>
                        <input wire:model="email_notificacion" type="email" id="email_notificacion" 
                               class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        @error('email_notificacion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            
            <!-- Paso 2: Detalles del Caso -->
            <div x-show="currentStep === 2" class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900">Detalles del Caso</h3>
                
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                    <div>
                        <label for="torre_id" class="block text-sm font-medium text-gray-700">Torre *</label>
                        <select wire:model="torre_id" id="torre_id" 
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            <option value="">Seleccione...</option>
                            @foreach($torres as $torre)
                                <option value="{{ $torre->id }}">{{ $torre->nombre }}</option>
                            @endforeach
                        </select>
                        @error('torre_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label for="tipo_caso_id" class="block text-sm font-medium text-gray-700">Tipo de Caso *</label>
                        <select wire:model="tipo_caso_id" id="tipo_caso_id" 
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            <option value="">Seleccione...</option>
                            @foreach($tiposcaso as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                        @error('tipo_caso_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="sm:col-span-2">
                        <label for="justificacion" class="block text-sm font-medium text-gray-700">Justificación *</label>
                        <textarea wire:model="justificacion" id="justificacion" rows="4"
                                  class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                        @error('justificacion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        <p class="mt-2 text-sm text-gray-500">Por favor detalle la razón de las horas extras.</p>
                    </div>
                </div>
            </div>
            
            <!-- Paso 3: Fecha y Horas -->
            <div x-show="currentStep === 3" class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900">Fecha y Horas</h3>
                
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="fecha" class="block text-sm font-medium text-gray-700">Fecha *</label>
                        <input wire:model="fecha" type="date" id="fecha" 
                               class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        @error('fecha') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label for="hora_inicio" class="block text-sm font-medium text-gray-700">Hora de Inicio *</label>
                        <input wire:model="hora_inicio" type="time" id="hora_inicio" 
                               class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        @error('hora_inicio') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label for="hora_fin" class="block text-sm font-medium text-gray-700">Hora de Fin *</label>
                        <input wire:model="hora_fin" type="time" id="hora_fin" 
                               class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        @error('hora_fin') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="sm:col-span-2">
                        <p class="text-sm text-gray-500">
                            Total de horas: 
                            <span x-data="{ 
                                get horasCalculadas() {
                                    if (!@js($hora_inicio) || !@js($hora_fin)) return '0h 0m';
                                    
                                    const inicio = new Date(`2000-01-01T${@js($hora_inicio)}`);
                                    const fin = new Date(`2000-01-01T${@js($hora_fin)}`);
                                    
                                    if (fin <= inicio) return 'Hora fin debe ser mayor a inicio';
                                    
                                    const diff = (fin - inicio) / 1000 / 60; // en minutos
                                    const horas = Math.floor(diff / 60);
                                    const minutos = diff % 60;
                                    
                                    return `${horas}h ${minutos}m`;
                                }
                            }" x-text="horasCalculadas"></span>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Botones de Navegación -->
            <div class="mt-8 flex justify-between">
                <button wire:click="previousStep" 
                        x-show="currentStep > 1"
                        type="button" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Anterior
                </button>
                
                <div>
                    <button wire:click="nextStep" 
                            x-show="currentStep < {{ $totalSteps }}"
                            type="button" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Siguiente
                    </button>
                    
                    <button wire:click="submitForm" 
                            x-show="currentStep === {{ $totalSteps }}"
                            type="button" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Enviar Solicitud
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mensaje de Éxito -->
        @if (session()->has('message'))
            <div class="mt-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
                {{ session('message') }}
            </div>
        @endif
    </div>
</div>
```

## Comandos para Crear Componentes Livewire

Para mantener la consistencia en el desarrollo de componentes similares, utiliza estos comandos:

```bash
# Crear componente Livewire básico
php artisan make:livewire FormularioHorasExtras

# Crear componente con pruebas automatizadas
php artisan make:livewire FormularioHorasExtras --test

# Crear un componente inline (sin vista separada)
php artisan make:livewire FormularioHorasExtras --inline
```

## Integración en la Aplicación

### Ruta para Acceder al Formulario

```php
// routes/web.php
Route::get('/solicitar-horas-extras', function () {
    return view('solicitar-horas-extras');
})->name('solicitar.horas.extras');
```

### Vista Principal que Contiene el Componente

```html
<!-- resources/views/solicitar-horas-extras.blade.php -->
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:formulario-horas-extras />
        </div>
    </div>
</x-app-layout>
```

## Estilos con TailwindCSS

### Personalización de Colores

El formulario utiliza la paleta de verdes personalizada de POSITIVOS, definida en el archivo `tailwind.config.js`:

```javascript
// tailwind.config.js
module.exports = {
    theme: {
        extend: {
            colors: {
                green: {
                    50: '#f0fdf4',
                    100: '#dcfce7',
                    200: '#bbf7d0',
                    300: '#86efac',
                    400: '#4ade80',
                    500: '#22c55e',
                    600: '#16a34a',
                    700: '#15803d',
                    800: '#166534',
                    900: '#14532d',
                    950: '#052e16',
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}
```

### Componentes Reutilizables

Para mantener consistencia en los formularios, se recomienda crear componentes Blade reutilizables:

```html
<!-- resources/views/components/forms/input.blade.php -->
@props(['disabled' => false, 'error' => ''])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md' . ($error ? ' border-red-300' : '')]) !!}>

@if($error)
    <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
@endif
```

## Validaciones y Mensajes de Error

### Personalización de Mensajes

Para personalizar los mensajes de error, añade traducciones específicas para el contexto de horas extras:

```php
// resources/lang/es/validation.php
return [
    // ...
    'custom' => [
        'hora_inicio' => [
            'date_format' => 'El formato de hora de inicio debe ser HH:MM.',
        ],
        'hora_fin' => [
            'after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ],
        'justificacion' => [
            'min' => 'La justificación debe tener al menos :min caracteres.',
        ],
    ],
];
```

## Implementación de Cálculo de Horas en Alpine.js

Alpine.js se utiliza para cálculos dinámicos en el cliente, como el total de horas extras:

```javascript
// Extracto de la funcionalidad Alpine.js
x-data="{ 
    get horasCalculadas() {
        if (!inicio || !fin) return '0h 0m';
        
        const inicio = new Date(`2000-01-01T${horaInicio}`);
        const fin = new Date(`2000-01-01T${horaFin}`);
        
        if (fin <= inicio) return 'Hora fin debe ser mayor a inicio';
        
        const diff = (fin - inicio) / 1000 / 60; // en minutos
        const horas = Math.floor(diff / 60);
        const minutos = diff % 60;
        
        return `${horas}h ${minutos}m`;
    }
}"
```

## Buenas Prácticas para Formularios Multi-paso

1. **Validación por pasos**: Validar solo los campos del paso actual para mejorar la experiencia
2. **Indicadores visuales claros**: Utilizar barra de progreso y numeración de pasos
3. **Mensajes de error contextuales**: Mostrar errores específicos cerca del campo correspondiente
4. **Diseño responsive**: Garantizar buena experiencia en dispositivos móviles
5. **Persistencia temporal**: Guardar datos en sesión en caso de navegación accidental

## Pruebas Automatizadas

### Ejemplo de Prueba para el Componente

```php
// tests/Feature/FormularioHorasExtrasTest.php
namespace Tests\Feature;

use App\Http\Livewire\FormularioHorasExtras;
use App\Models\JefeInmediato;
use App\Models\Torre;
use App\Models\TipoCaso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FormularioHorasExtrasTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_navegar_entre_pasos()
    {
        $jefe = JefeInmediato::factory()->create();
        $torre = Torre::factory()->create();
        $tipoCaso = TipoCaso::factory()->create();
        
        Livewire::test(FormularioHorasExtras::class)
            ->set('documento', '1234567890')
            ->set('nombre', 'Juan Pérez')
            ->set('jefe_inmediato_id', $jefe->id)
            ->set('email_notificacion', 'juan@ejemplo.com')
            ->call('nextStep')
            ->assertSet('currentStep', 2)
            
            ->set('torre_id', $torre->id)
            ->set('tipo_caso_id', $tipoCaso->id)
            ->set('justificacion', 'Necesario para completar proyecto prioritario')
            ->call('nextStep')
            ->assertSet('currentStep', 3)
            
            ->set('fecha', now()->format('Y-m-d'))
            ->set('hora_inicio', '18:00')
            ->set('hora_fin', '20:00')
            ->call('submitForm')
            ->assertEmitted('message');
    }
}
```

## Referencias

- [Documentación de Livewire](https://laravel-livewire.com/docs)
- [Documentación de Alpine.js](https://alpinejs.dev/start-here)
- [Documentación de TailwindCSS](https://tailwindcss.com/docs)
- [Guía de Formularios en Laravel](https://laravel.com/docs/10.x/validation)
