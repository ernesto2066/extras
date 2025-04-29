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

        $actividad->estado = Actividad::ESTADO_APROBADA_COORDINADOR;
        $actividad->comentarios = $comentarios;
        $actividad->aprobador_id = Auth::id();
        $actividad->fecha_aprobacion = Carbon::now();
        $actividad->save();

        // Enviar notificación si hay email de notificación
        if ($actividad->email_notificacion) {
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

        $actividad->estado = Actividad::ESTADO_RECHAZADA_COORDINADOR;
        $actividad->comentarios = $comentarios;
        $actividad->aprobador_id = Auth::id();
        $actividad->fecha_aprobacion = Carbon::now();
        $actividad->save();

        // Enviar notificación si hay email de notificación
        if ($actividad->email_notificacion) {
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
        if ($actividad->email_notificacion) {
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
        if ($actividad->email_notificacion) {
            $this->enviarNotificacionRechazo($actividad, 'final');
        }

        return true;
    }

    /**
     * Enviar notificación de aprobación
     */
    private function enviarNotificacionAprobacion(Actividad $actividad, string $nivel): void
    {
        $notifiable = new AnonymousNotifiable();
        $notifiable->route('mail', $actividad->email_notificacion);
        
        Notification::send($notifiable, new HoraExtraAprobada($actividad, $nivel));
        
        // Mensaje de confirmación visual con Filament
        FilamentNotification::make()
            ->title('Correo enviado')
            ->body('Notificación de aprobación enviada a: ' . $actividad->email_notificacion)
            ->success()
            ->send();
    }

    /**
     * Enviar notificación de rechazo
     */
    private function enviarNotificacionRechazo(Actividad $actividad, string $nivel): void
    {
        $notifiable = new AnonymousNotifiable();
        $notifiable->route('mail', $actividad->email_notificacion);
        
        Notification::send($notifiable, new HoraExtraRechazada($actividad, $nivel));
        
        // Mensaje de confirmación visual con Filament
        FilamentNotification::make()
            ->title('Correo enviado')
            ->body('Notificación de rechazo enviada a: ' . $actividad->email_notificacion)
            ->success()
            ->send();
    }
}
