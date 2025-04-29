<?php

namespace App\Notifications;

use App\Models\Actividad;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HoraExtraRechazada extends Notification implements ShouldQueue
{
    use Queueable;

    protected Actividad $actividad;
    protected string $nivel;

    /**
     * Create a new notification instance.
     */
    public function __construct(Actividad $actividad, string $nivel = 'final')
    {
        $this->actividad = $actividad;
        $this->nivel = $nivel;
        
        // Log detallado al construir la notificación
        \Illuminate\Support\Facades\Log::debug('HoraExtraRechazada::__construct - Notificación creada', [
            'actividad_id' => $actividad->id,
            'nivel' => $nivel,
            'email' => $actividad->email_notificacion,
            'driver_correo' => config('mail.default'),
            'fecha_hora' => now()->toDateTimeString(),
            'app_env' => config('app.env')
        ]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Log detallado del canal de envío
        \Illuminate\Support\Facades\Log::debug('HoraExtraRechazada::via - Definiendo canales de envío', [
            'canal' => 'mail',
            'actividad_id' => $this->actividad->id,
            'notifiable_type' => get_class($notifiable),
            'fecha_hora' => now()->toDateTimeString()
        ]);
        
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Log detallado antes de generar el mensaje
        \Illuminate\Support\Facades\Log::debug('HoraExtraRechazada::toMail - Generando mensaje de correo', [
            'actividad_id' => $this->actividad->id,
            'email_destino' => $notifiable->routes['mail'] ?? 'No especificado',
            'driver_correo' => config('mail.default'),
            'fecha_hora' => now()->toDateTimeString()
        ]);
        
        $etapa = $this->nivel === 'coordinador' ? 'por Coordinador' : 'Final';
        
        return (new MailMessage)
            ->subject('Hora Extra Rechazada - Claro Data Center')
            ->greeting('Hola ' . $this->actividad->nombre_completo)
            ->line('Lamentamos informarte que tu solicitud de hora extra ha sido rechazada ' . $etapa . '.')
            ->line('Detalles de la solicitud:')
            ->line('ID: ' . $this->actividad->id)
            ->line('Fecha: ' . $this->actividad->fecha_ejecucion)
            ->line('Hora inicio: ' . $this->actividad->hora_inicio)
            ->line('Hora fin: ' . $this->actividad->hora_fin)
            ->line('Cliente: ' . $this->actividad->cliente)
            ->when($this->actividad->comentarios, function ($message) {
                return $message->line('Motivo: ' . $this->actividad->comentarios);
            })
            ->action('Ver Detalle', url('/'))
            ->line('Si tienes alguna pregunta, por favor contacta a tu jefe inmediato.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'actividad_id' => $this->actividad->id,
            'estado' => $this->actividad->estado,
            'aprobador' => $this->actividad->aprobador->name ?? 'Sistema',
        ];
    }
}
