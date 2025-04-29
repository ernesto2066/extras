<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\User;
use App\Notifications\AnonymousNotifiable;
use App\Notifications\HoraExtraAprobada;
use App\Notifications\HoraExtraRechazada;
use Carbon\Carbon;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class AprobacionHorasService
{
    /**
     * Aprobar una hora extra por un coordinador
     */
    public function aprobarPorCoordinador(Actividad $actividad, ?string $comentarios = null): bool
    {
        if (!$actividad->puedeSerAprobadaPorCoordinador()) {
            return false;
        }

        // Verificar si el usuario tiene permiso
        if (!Auth::user()->can('aprobar horas extras')) {
            return false;
        }

        // Guardar datos originales para enviar notificación
        $emailNotificacion = $actividad->email_notificacion;
        $nombreCompleto = $actividad->nombre_completo;

        // Actualizar actividad
        $actividad->estado = Actividad::ESTADO_APROBADA_COORDINADOR;
        $actividad->comentarios = $comentarios;
        $actividad->aprobador_id = Auth::id();
        $actividad->fecha_aprobacion = Carbon::now();
        $actividad->save();

        // Enviar notificación si hay email de notificación
        if ($emailNotificacion) {
            // Refrescar el modelo para tener datos actualizados
            $actividad->refresh();
            $this->enviarNotificacionAprobacion($actividad, 'coordinador');
        }

        return true;
    }

    /**
     * Rechazar una hora extra por un coordinador
     */
    public function rechazarPorCoordinador(Actividad $actividad, string $comentarios): bool
    {
        if (!$actividad->puedeSerAprobadaPorCoordinador()) {
            return false;
        }

        // Verificar si el usuario tiene permiso
        if (!Auth::user()->can('rechazar horas extras')) {
            return false;
        }

        // Guardar datos originales para enviar notificación
        $emailNotificacion = $actividad->email_notificacion;
        $nombreCompleto = $actividad->nombre_completo;
        
        // Actualizar actividad
        $actividad->estado = Actividad::ESTADO_RECHAZADA_COORDINADOR;
        $actividad->comentarios = $comentarios;
        $actividad->aprobador_id = Auth::id();
        $actividad->fecha_aprobacion = Carbon::now();
        $actividad->save();

        // Enviar notificación si hay email de notificación
        if ($emailNotificacion) {
            // Refrescar el modelo para tener datos actualizados
            $actividad->refresh();
            $this->enviarNotificacionRechazo($actividad, 'coordinador');
        }

        return true;
    }

    /**
     * Aprobar una hora extra por un administrador (aprobación final)
     */
    public function aprobarFinal(Actividad $actividad, ?string $comentarios = null): bool
    {
        if (!$actividad->puedeSerAprobadaPorAdministrador()) {
            return false;
        }

        // Verificar si el usuario tiene permiso
        if (!Auth::user()->can('aprobar horas extras')) {
            return false;
        }

        // Guardar datos originales para enviar notificación
        $emailNotificacion = $actividad->email_notificacion;
        $nombreCompleto = $actividad->nombre_completo;
        
        // Actualizar actividad
        $actividad->estado = Actividad::ESTADO_APROBADA_FINAL;
        
        // Si hay comentarios nuevos, actualizar o añadir
        if ($comentarios) {
            $actividad->comentarios = $actividad->comentarios 
                ? $actividad->comentarios . "\n[Aprobación final]: " . $comentarios
                : $comentarios;
        }
        
        $actividad->aprobador_id = Auth::id();
        $actividad->fecha_aprobacion = Carbon::now();
        $actividad->save();

        // Enviar notificación si hay email de notificación
        if ($emailNotificacion) {
            // Refrescar el modelo para tener datos actualizados
            $actividad->refresh();
            $this->enviarNotificacionAprobacion($actividad, 'final');
        }

        return true;
    }

    /**
     * Rechazar una hora extra por un administrador (rechazo final)
     */
    public function rechazarFinal(Actividad $actividad, string $comentarios): bool
    {
        if (!$actividad->puedeSerAprobadaPorAdministrador()) {
            return false;
        }

        // Verificar si el usuario tiene permiso
        if (!Auth::user()->can('rechazar horas extras')) {
            return false;
        }

        // Guardar datos originales para enviar notificación
        $emailNotificacion = $actividad->email_notificacion;
        $nombreCompleto = $actividad->nombre_completo;
        
        // Actualizar actividad
        $actividad->estado = Actividad::ESTADO_RECHAZADA_FINAL;
        
        // Si hay comentarios nuevos, actualizar o añadir
        if ($comentarios) {
            $actividad->comentarios = $actividad->comentarios 
                ? $actividad->comentarios . "\n[Rechazo final]: " . $comentarios
                : $comentarios;
        }
        
        $actividad->aprobador_id = Auth::id();
        $actividad->fecha_aprobacion = Carbon::now();
        $actividad->save();

        // Enviar notificación si hay email de notificación
        if ($emailNotificacion) {
            // Refrescar el modelo para tener datos actualizados
            $actividad->refresh();
            $this->enviarNotificacionRechazo($actividad, 'final');
        }

        return true;
    }

    /**
     * Enviar notificación de aprobación
     */
    private function enviarNotificacionAprobacion(Actividad $actividad, string $nivel): void
    {
        \Illuminate\Support\Facades\Log::info('INICIO: Proceso de envío de notificación de aprobación', [
            'email' => $actividad->email_notificacion,
            'actividad_id' => $actividad->id,
            'nivel' => $nivel,
            'estado' => $actividad->estado,
            'fecha_hora' => now()->toDateTimeString()
        ]);
        
        try {
            // Crear un notifiable anónimo para el correo
            \Illuminate\Support\Facades\Log::info('PASO 1: Creando notifiable anónimo');
            $notifiable = new AnonymousNotifiable();
            $notifiable->route('mail', $actividad->email_notificacion);
            
            // Verificar configuración de correo
            \Illuminate\Support\Facades\Log::info('PASO 2: Verificando configuración de correo', [
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name')
            ]);
            
            // Forzar el envío inmediato sin usar colas para asegurar que se envíe
            \Illuminate\Support\Facades\Log::info('PASO 3: Creando notificación');
            $notification = new HoraExtraAprobada($actividad, $nivel);
            
            \Illuminate\Support\Facades\Log::info('PASO 4: Generando mensaje de correo');
            $mailMessage = $notification->toMail($notifiable);
            
            \Illuminate\Support\Facades\Log::info('PASO 5: Enviando correo directamente');
            $mailMessage->send();
            \Illuminate\Support\Facades\Log::info('PASO 5: Correo enviado directamente con éxito');
            
            // También intentamos con el método estándar como respaldo
            \Illuminate\Support\Facades\Log::info('PASO 6: Enviando correo con Notification::send');
            Notification::send($notifiable, $notification);
            \Illuminate\Support\Facades\Log::info('PASO 6: Correo enviado con Notification::send con éxito');
            
            // Registrar en el log
            \Illuminate\Support\Facades\Log::info('PROCESO COMPLETO: Notificación de aprobación enviada', [
                'email' => $actividad->email_notificacion,
                'actividad_id' => $actividad->id,
                'nivel' => $nivel,
                'fecha_hora' => now()->toDateTimeString()
            ]);
            
            // Mensaje de confirmación visual con Filament
            FilamentNotification::make()
                ->title('Correo enviado')
                ->body('Notificación de aprobación enviada a: ' . $actividad->email_notificacion)
                ->success()
                ->send();
        } catch (\Exception $e) {
            // Registrar el error detalladamente
            \Illuminate\Support\Facades\Log::error('ERROR EN EL PROCESO: Error al enviar notificación de aprobación', [
                'email' => $actividad->email_notificacion,
                'actividad_id' => $actividad->id,
                'error' => $e->getMessage(),
                'codigo_error' => $e->getCode(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'estado_actividad' => $actividad->estado,
                'mail_driver' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_config' => json_encode(config('mail')),
                'fecha_hora' => now()->toDateTimeString()
            ]);
            
            // Notificar del error pero no interrumpir el flujo
            FilamentNotification::make()
                ->title('Error al enviar correo')
                ->body('No se pudo enviar la notificación a: ' . $actividad->email_notificacion)
                ->danger()
                ->send();
        }
    }

    /**
     * Enviar notificación de rechazo
     */
    private function enviarNotificacionRechazo(Actividad $actividad, string $nivel): void
    {
        \Illuminate\Support\Facades\Log::info('INICIO: Proceso de envío de notificación de rechazo', [
            'email' => $actividad->email_notificacion,
            'actividad_id' => $actividad->id,
            'nivel' => $nivel,
            'estado' => $actividad->estado,
            'fecha_hora' => now()->toDateTimeString()
        ]);
        
        try {
            // Crear un notifiable anónimo para el correo
            \Illuminate\Support\Facades\Log::info('PASO 1: Creando notifiable anónimo');
            $notifiable = new AnonymousNotifiable();
            $notifiable->route('mail', $actividad->email_notificacion);
            
            // Verificar configuración de correo
            \Illuminate\Support\Facades\Log::info('PASO 2: Verificando configuración de correo', [
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name')
            ]);
            
            // Forzar el envío inmediato sin usar colas para asegurar que se envíe
            \Illuminate\Support\Facades\Log::info('PASO 3: Creando notificación');
            $notification = new HoraExtraRechazada($actividad, $nivel);
            
            \Illuminate\Support\Facades\Log::info('PASO 4: Generando mensaje de correo');
            $mailMessage = $notification->toMail($notifiable);
            
            \Illuminate\Support\Facades\Log::info('PASO 5: Enviando correo directamente');
            $mailMessage->send();
            \Illuminate\Support\Facades\Log::info('PASO 5: Correo enviado directamente con éxito');
            
            // También intentamos con el método estándar como respaldo
            \Illuminate\Support\Facades\Log::info('PASO 6: Enviando correo con Notification::send');
            Notification::send($notifiable, $notification);
            \Illuminate\Support\Facades\Log::info('PASO 6: Correo enviado con Notification::send con éxito');
            
            // Registrar en el log
            \Illuminate\Support\Facades\Log::info('PROCESO COMPLETO: Notificación de rechazo enviada', [
                'email' => $actividad->email_notificacion,
                'actividad_id' => $actividad->id,
                'nivel' => $nivel,
                'fecha_hora' => now()->toDateTimeString()
            ]);
            
            // Mensaje de confirmación visual con Filament
            FilamentNotification::make()
                ->title('Correo enviado')
                ->body('Notificación de rechazo enviada a: ' . $actividad->email_notificacion)
                ->success()
                ->send();
        } catch (\Exception $e) {
            // Registrar el error detalladamente
            \Illuminate\Support\Facades\Log::error('ERROR EN EL PROCESO: Error al enviar notificación de rechazo', [
                'email' => $actividad->email_notificacion,
                'actividad_id' => $actividad->id,
                'error' => $e->getMessage(),
                'codigo_error' => $e->getCode(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'estado_actividad' => $actividad->estado,
                'mail_driver' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_config' => json_encode(config('mail')),
                'fecha_hora' => now()->toDateTimeString()
            ]);
            
            // Notificar del error pero no interrumpir el flujo
            FilamentNotification::make()
                ->title('Error al enviar correo')
                ->body('No se pudo enviar la notificación a: ' . $actividad->email_notificacion)
                ->danger()
                ->send();
        }
    }
}
