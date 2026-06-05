<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AceptacionColaboracion extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array  $solicitudData   Datos de la convocatoria
     * @param  array  $postulanteData  Datos del artista (debe incluir 'email' y 'nombre')
     */
    public function __construct(
        public readonly array $solicitudData,
        public readonly array $postulanteData
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '\u{1F3B5} Mango Music \u2014 \u00a1Tu postulaci\u00f3n fue aceptada!',
            to: [$this->postulanteData['email']],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.aceptacion_colaboracion',
            with: [
                'solicitud' => $this->solicitudData,
                'postulante' => $this->postulanteData,
            ],
        );
    }
}
