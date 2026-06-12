<?php

namespace App\Mail;

use App\Models\Postulante;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdmissionResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public $postulante;

    /**
     * Create a new message instance.
     */
    public function __construct(Postulante $postulante)
    {
        $this->postulante = $postulante;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $statusText = '';
        switch ($this->postulante->estado_admision) {
            case 'admitido_primera_opcion':
            case 'admitido_segunda_opcion':
                $statusText = '¡Admitido!';
                break;
            case 'no_admitido':
                $statusText = 'Resultados Académicos';
                break;
            case 'reprobado':
                $statusText = 'Resultados Académicos';
                break;
            default:
                $statusText = 'Resultados';
        }

        return new Envelope(
            subject: "CUP - Resultados de Admisión: {$statusText} - {$this->postulante->nombres_apellidos}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admission_result',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
