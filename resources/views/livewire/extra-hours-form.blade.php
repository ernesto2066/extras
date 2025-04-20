<div class="min-h-screen bg-gray-900 pt-4 px-4 sm:px-6 lg:px-8 relative">
    <!-- Logo en la esquina superior derecha como posición absoluta -->
    <div class="absolute right-8 top-5">
        <img src="{{ asset('images/positivos-logo.png') }}" alt="Positivo" class="h-20 md:h-24 rounded-lg shadow-lg transition duration-300 ease-in-out hover:shadow-xl">
    </div>
    
    <!-- Formulario centrado -->
    <div class="max-w-3xl mx-auto pt-2">
        <div class="bg-white shadow-2xl rounded-lg overflow-hidden">
            <div class="p-6 md:p-8">
        <!-- Encabezado del formulario -->
        
        <!-- Contenido del formulario -->
        <div class="p-6 md:p-8">
        
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6 text-center">
            Claro Data Center- Registrar Horas Extras
        </h2>

        @if (session()->has('message'))
            <div class="mb-4 rounded-md bg-green-50 p-4">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('warning'))
            <div class="mb-4 rounded-md bg-yellow-100 p-4 text-sm text-yellow-700">
                {{ session('warning') }}
            </div>
        @endif

        <form wire:submit.prevent="save">
            @csrf

            {{-- Flujo de pasos con Alpine.js --}}
            <div x-data="{ currentStep: 1 }">

                {{-- Indicadores de Pasos --}}
                <div class="mb-6 border-b border-gray-300">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button type="button" @click.prevent="currentStep = 1" :class="{ 'border-green-500 text-green-600': currentStep === 1, 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': currentStep !== 1 }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            1. Información Personal
                        </button>
                        <button type="button" @click.prevent="currentStep = 2" :class="{ 'border-green-500 text-green-600': currentStep === 2, 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': currentStep !== 2 }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            2. Detalles del Caso
                        </button>
                        <button type="button" @click.prevent="currentStep = 3" :class="{ 'border-green-500 text-green-600': currentStep === 3, 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': currentStep !== 3 }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            3. Fecha y Horas
                        </button>
                    </nav>
                </div>

                {{-- Contenido de los Pasos --}}
                <div>
                    {{-- Paso 1: Información Personal --}}
                    <div x-show="currentStep === 1">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Información Personal</h3>
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Documento de Identidad -->
                            <div>
                                <label for="documento_identidad" class="block text-sm font-medium text-gray-700">Documento De Identidad <span class="text-red-600">*</span></label>
                                <input type="text" wire:model.lazy="documento_identidad" id="documento_identidad" {{-- Usar .lazy --}}
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('documento_identidad') border-red-500 @enderror" {{-- Color foco verde --}}
                                       placeholder="Escriba su respuesta">
                                @error('documento_identidad') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <!-- Nombre Completo -->
                            <div>
                                <label for="nombre_completo" class="block text-sm font-medium text-gray-700">Nombre Completo <span class="text-red-600">*</span></label>
                                <input type="text" wire:model.lazy="nombre_completo" id="nombre_completo" {{-- Usar .lazy --}}
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('nombre_completo') border-red-500 @enderror" {{-- Color foco verde --}}
                                       placeholder="Escriba su respuesta">
                                @error('nombre_completo') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <!-- Jefe Inmediato -->
                            <div>
                                <label for="jefe_inmediato_id" class="block text-sm font-medium text-gray-700">Jefe Inmediato <span class="text-red-600">*</span></label>
                                <select wire:model.lazy="jefe_inmediato_id" id="jefe_inmediato_id" {{-- Usar .lazy --}}
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('jefe_inmediato_id') border-red-500 @enderror"> {{-- Color foco verde --}}
                                    <option value="">Seleccione Jefe Inmediato</option>
                                    @foreach($jefesInmediatos as $jefe)
                                        <option value="{{ $jefe->id }}">{{ $jefe->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('jefe_inmediato_id') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        {{-- Botón Siguiente (Paso 1) --}}
                        <div class="mt-6 flex justify-end">
                            <button type="button" @click="currentStep = 2"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Siguiente &rarr;
                            </button>
                        </div>
                    </div>

                    {{-- Paso 2: Detalles del Caso --}}
                    <div x-show="currentStep === 2">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Detalles del Caso</h3>
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Torre -->
                            <div>
                                <label for="torre_id" class="block text-sm font-medium text-gray-700">Torre <span class="text-red-600">*</span></label>
                                <select wire:model.lazy="torre_id" id="torre_id" {{-- Usar .lazy --}}
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('torre_id') border-red-500 @enderror"> {{-- Color foco verde --}}
                                    <option value="">Seleccione una Torre</option>
                                    @foreach($torres as $torre)
                                        <option value="{{ $torre->id }}">{{ $torre->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('torre_id') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <!-- Tipo de Caso -->
                            <div>
                                <label for="tipo_caso_id" class="block text-sm font-medium text-gray-700">Tipo de Caso <span class="text-red-600">*</span></label>
                                <select wire:model.lazy="tipo_caso_id" id="tipo_caso_id" {{-- Usar .lazy --}}
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('tipo_caso_id') border-red-500 @enderror"> {{-- Color foco verde --}}
                                    <option value="">Seleccione un Tipo de Caso</option>
                                    @foreach($tiposCaso as $tipo)
                                        <option value="{{ $tipo->id }}">{{ $tipo->nombre ?? $tipo->descripcion }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_caso_id') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <!-- Cliente -->
                            <div>
                                <label for="cliente" class="block text-sm font-medium text-gray-700">Cliente <span class="text-red-600">*</span></label>
                                <input type="text" wire:model.lazy="cliente" id="cliente" {{-- Usar .lazy --}}
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('cliente') border-red-500 @enderror" {{-- Color foco verde --}}
                                       placeholder="Escriba su respuesta">
                                @error('cliente') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <!-- Número de Casos Gestionados -->
                            <div>
                                <label for="numero_casos" class="block text-sm font-medium text-gray-700">Número de Casos Gestionados <span class="text-red-600">*</span></label>
                                <input type="text" wire:model.lazy="numero_casos" id="numero_casos" {{-- Usar .lazy --}}
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('numero_casos') border-red-500 @enderror" {{-- Color foco verde --}}
                                       placeholder="Ej: 12345/67890">
                                <small class="form-text text-muted">Ingrese múltiples códigos separados por /</small>
                                @error('numero_casos') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <!-- Descripción de la Actividad -->
                            <div>
                                <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción de la Actividad <span class="text-red-600">*</span></label>
                                <textarea wire:model.lazy="descripcion" id="descripcion" rows="4" {{-- Usar .lazy --}}
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('descripcion') border-red-500 @enderror" {{-- Color foco verde --}}
                                          placeholder="Escriba su respuesta"></textarea>
                                @error('descripcion') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        {{-- Botones Anterior/Siguiente (Paso 2) --}}
                        <div class="mt-6 flex justify-between">
                            <button type="button" @click="currentStep = 1"
                                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                &larr; Anterior
                            </button>
                            <button type="button" @click="currentStep = 3"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Siguiente &rarr;
                            </button>
                        </div>
                    </div>

                    {{-- Paso 3: Fecha y Horas --}}
                    <div x-show="currentStep === 3">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Fecha y Horas</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Fecha de Ejecución -->
                            <div>
                                <label for="fecha_ejecucion" class="block text-sm font-medium text-gray-700">Fecha de Ejecución <span class="text-red-600">*</span></label>
                                <input type="date" wire:model.lazy="fecha_ejecucion" id="fecha_ejecucion" {{-- Usar .lazy --}}
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('fecha_ejecucion') border-red-500 @enderror"> {{-- Color foco verde --}}
                                @error('fecha_ejecucion') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <!-- Hora de Inicio -->
                            <div>
                                <label for="hora_inicio" class="block text-sm font-medium text-gray-700">Hora de Inicio <span class="text-red-600">*</span></label>
                                <input type="time" wire:model.lazy="hora_inicio" id="hora_inicio" {{-- Usar .lazy --}}
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('hora_inicio') border-red-500 @enderror"> {{-- Color foco verde --}}
                                @error('hora_inicio') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <!-- Hora de Fin -->
                            <div>
                                <label for="hora_fin" class="block text-sm font-medium text-gray-700">Hora de Fin <span class="text-red-600">*</span></label>
                                <input type="time" wire:model.lazy="hora_fin" id="hora_fin" {{-- Usar .lazy --}}
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('hora_fin') border-red-500 @enderror"> {{-- Color foco verde --}}
                                @error('hora_fin') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         {{-- Botón Anterior y Guardar (Paso 3) --}}
                        <div class="mt-6 flex justify-between">
                            <button type="button" @click="currentStep = 2"
                                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                &larr; Anterior
                            </button>
                            <button type="submit"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"> {{-- Botón verde --}}
                                Guardar Actividad
                            </button>
                        </div>
                    </div>
                </div>

            </div> {{-- Fin del flujo de pasos --}}

            <!-- Mensaje de Éxito -->
            @if (session()->has('message'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="mt-4 rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </form>
                </div>
            </div>
        </div>
    </div>
</div>
