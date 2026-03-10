<?php

namespace App\Notifications;

use App\Models\BitacoraTrabajo;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NuevaNotaTarea extends Notification
{
    use Queueable;

    public $tarea;
    public $nota;

    /**
     * Create a new notification instance.
     */
    public function __construct(BitacoraTrabajo $tarea, $nota)
    {
        $this->tarea = $tarea;
        $this->nota = $nota;
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
        $mecanico = $this->tarea->mecanico ? ($this->tarea->mecanico->persona ? $this->tarea->mecanico->persona->nombres . ' ' . $this->tarea->mecanico->persona->apellidos : $this->tarea->mecanico->name) : 'Un mecánico';

        return [
            'tarea_id' => $this->tarea->id,
            'orden_id' => $this->tarea->orden_trabajo_id,
            'mensaje' => "{$mecanico} agregó una nota a la tarea '{$this->tarea->descripcion}' de la OT #{$this->tarea->orden_trabajo_id}: '{$this->nota}'",
            'url' => route('panel.operaciones.ordenes_trabajo.show', $this->tarea->orden_trabajo_id)
        ];
    }
}
