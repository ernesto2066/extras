<?php

namespace App\Notifications;

use App\Models\Actividad;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HoraExtraRegistrada extends Notification
{
    use Queueable;

    protected $actividad;

    /**
     * Create a new notification instance.
     */
    public function __construct(Actividad $actividad)
    {
        $this->actividad = $actividad;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Registro de Hora Extra - POSITIVOS')
            ->greeting('Hola ' . $this->actividad->nombre_completo)
            ->line('Su solicitud de hora extra ha sido registrada exitosamente.')
            ->line('Número de registro: ' . $this->actividad->id)
            ->line('Fecha de ejecución: ' . $this->actividad->fecha_ejecucion)
            ->line('Hora inicio: ' . $this->actividad->hora_inicio)
            ->line('Hora fin: ' . $this->actividad->hora_fin)
            ->line('Estado actual: Pendiente de aprobación')
            ->line('Usted recibirá una notificación cuando su solicitud sea revisada por un coordinador o administrador.')
            ->line('Gracias por usar nuestro sistema de gestión de horas extras.');
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
        ];
    }
}
