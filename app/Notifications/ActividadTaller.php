<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ActividadTaller extends Notification
{
    use Queueable;

    public $mensaje;
    public $url;
    public $tipo; // 'orden_creada', 'orden_finalizada', 'pago_recibido', etc.

    /**
     * Create a new notification instance.
     */
    public function __construct($mensaje, $url = '#', $tipo = 'actividad_taller')
    {
        $this->mensaje = $mensaje;
        $this->url = $url;
        $this->tipo = $tipo;
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
        return [
            'mensaje' => $this->mensaje,
            'url' => $this->url,
            'tipo' => $this->tipo
        ];
    }
}
