<?php

namespace App\Filament\Resources\ActividadResource\Pages;

use App\Filament\Resources\ActividadResource;
use App\Models\Actividad;
use App\Notifications\AnonymousNotifiable;
use App\Notifications\HoraExtraAprobada;
use App\Notifications\HoraExtraRechazada;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class EditActividad extends EditRecord
{
    protected static string $resource = ActividadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        try {
            // Guardar el estado actual antes de la actualización
            $estadoAnterior = $this->record->estado ?? Actividad::ESTADO_PENDIENTE;
            $emailNotificacion = $this->record->email_notificacion;
            
            // Llamar al método save original
            parent::save($shouldRedirect, $shouldSendSavedNotification);
            
            // Obtener el registro actualizado de la base de datos
            $this->record->refresh();
            
            // Verificar si el estado cambió
            $nuevoEstado = $this->record->estado;
            
            if ($nuevoEstado !== $estadoAnterior && $nuevoEstado !== Actividad::ESTADO_PENDIENTE && $emailNotificacion) {
                // Actualizar el aprobador y la fecha si no se establecieron
                if (!$this->record->aprobador_id || !$this->record->fecha_aprobacion) {
                    $this->record->aprobador_id = Auth::id();
                    $this->record->fecha_aprobacion = Carbon::now();
                    $this->record->saveQuietly();
                }
                
                // Enviar notificación según el nuevo estado
                if ($nuevoEstado === Actividad::ESTADO_APROBADA_COORDINADOR || $nuevoEstado === Actividad::ESTADO_APROBADA_FINAL) {
                    $this->enviarNotificacionAprobacion($nuevoEstado);
                } elseif ($nuevoEstado === Actividad::ESTADO_RECHAZADA_COORDINADOR || $nuevoEstado === Actividad::ESTADO_RECHAZADA_FINAL) {
                    $this->enviarNotificacionRechazo($nuevoEstado);
                }
                
                // Registrar en el log
                Log::info('Notificación enviada por cambio de estado en el formulario de edición', [
                    'actividad_id' => $this->record->id,
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $nuevoEstado,
                    'email' => $emailNotificacion
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar el formulario de edición: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Enviar notificación de aprobación
     */
    private function enviarNotificacionAprobacion(string $estado): void
    {
        $nivel = $estado === Actividad::ESTADO_APROBADA_COORDINADOR ? 'coordinador' : 'final';
        
        $notifiable = new AnonymousNotifiable();
        $notifiable->route('mail', $this->record->email_notificacion);
        
        Notification::send($notifiable, new HoraExtraAprobada($this->record, $nivel));
        
        // Mostrar notificación en la interfaz
        FilamentNotification::make()
            ->title('Correo enviado')
            ->body('Notificación de aprobación enviada a: ' . $this->record->email_notificacion)
            ->success()
            ->send();
    }
    
    /**
     * Enviar notificación de rechazo
     */
    private function enviarNotificacionRechazo(string $estado): void
    {
        $nivel = $estado === Actividad::ESTADO_RECHAZADA_COORDINADOR ? 'coordinador' : 'final';
        
        $notifiable = new AnonymousNotifiable();
        $notifiable->route('mail', $this->record->email_notificacion);
        
        Notification::send($notifiable, new HoraExtraRechazada($this->record, $nivel));
        
        // Mostrar notificación en la interfaz
        FilamentNotification::make()
            ->title('Correo enviado')
            ->body('Notificación de rechazo enviada a: ' . $this->record->email_notificacion)
            ->success()
            ->send();
    }
}
