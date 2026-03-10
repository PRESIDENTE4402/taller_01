<?php

namespace App\Mail;

use App\Models\OrdenTrabajo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrdenTrabajoStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $orden;
    public $tipo; // 'listo', 'fase', 'cotizacion'
    public $customBody;
    public $customSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(OrdenTrabajo $orden, $tipo = 'fase', $body = null, $subject = null)
    {
        $this->orden = $orden;
        $this->tipo = $tipo;
        $this->customBody = $body;
        $this->customSubject = $subject;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->customSubject;

        if (!$subject) {
            $subject = match ($this->tipo) {
                'listo' => "¡Tu vehículo ya está listo! - Orden #{$this->orden->codigo_orden}",
                'cotizacion' => "Cotización de Servicio - Orden #{$this->orden->codigo_orden}",
                default => "Actualización de tu vehículo en taller - Orden #{$this->orden->codigo_orden}",
            };
        }

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.orden_status',
            with: [
                'body' => $this->customBody
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
