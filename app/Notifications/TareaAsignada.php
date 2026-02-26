<?php

namespace App\Notifications;

use App\Models\BitacoraTrabajo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TareaAsignada extends Notification
{
    use Queueable;

    public $tarea;

    /**
     * Create a new notification instance.
     */
    public function __construct(BitacoraTrabajo $tarea)
    {
        $this->tarea = $tarea;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $ordenText = $this->tarea->orden_trabajo_id ? ' de la OT #' . $this->tarea->orden_trabajo_id : '';
        return [
            'tarea_id' => $this->tarea->id,
            'mensaje' => 'Se te ha asignado una nueva tarea: ' . $this->tarea->descripcion . $ordenText,
            'url' => route('panel.mis_tareas.index')
        ];
    }
}
