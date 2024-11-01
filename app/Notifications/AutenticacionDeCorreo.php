<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AutenticacionDeCorreo extends Notification
{
    use Queueable;
    private $email;
    private $token;
    /**
     * Create a new notification instance.
     */
    public function __construct($email, $token)
    {
        $this->token = $token;
        $this->email = $email;
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
            ->subject('Expedientes ITO - Verifica tu correo electrónico')
            ->line('Se te ha creado una cuenta en la plataforma de Expedientes ITO')
            ->line('Haz clic en el botón de abajo para verificar tu dirección de correo electrónico.')
            ->action('Verifica tu correo', url('/verificar-correo/' . $this->email . '/' . $this->token))
            ->line('Tienes un día a partir de la fecha de este correo para verificarlo');
    }
}
