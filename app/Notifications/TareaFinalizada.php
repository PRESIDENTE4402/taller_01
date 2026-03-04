<?php

namespace App\Notifications;

use App\Models\BitacoraTrabajo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TareaFinalizada extends Notification
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
        try {
            $mecanico = $this->tarea->mecanico->name ?? 'Mecánico';
        } catch (\Exception $e) {
            $mecanico = 'Mecánico';
        }

        $url = '#';
        if ($this->tarea->orden_trabajo_id) {
            $url = route('panel.operaciones.ordenes_trabajo.show', $this->tarea->orden_trabajo_id);
        }

        return [
            'tarea_id' => $this->tarea->id,
            'mensaje' => "El mecánico {$mecanico} ha finalizado la tarea: {$this->tarea->descripcion}",
            'url' => $url
        ];
    }
}
