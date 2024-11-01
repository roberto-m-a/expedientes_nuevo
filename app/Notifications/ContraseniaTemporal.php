<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContraseniaTemporal extends Notification
{
    use Queueable;
    private $email;
    private $OTPassword;
    private $OTPassword_token;
    /**
     * Create a new notification instance.
     */
    public function __construct($email,$OTPassword, $OTPasswordToken)
    {
        //
        $this->email = $email;
        $this->OTPassword = $OTPassword;
        $this->OTPassword_token = $OTPasswordToken;
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
            ->subject('Expedientes ITO - Contraseña temporal')
            ->line('Ingresa con la siguiente contraseña temporal')
            ->line($this->OTPassword)
            ->action('Ingresa', url('/verificar-otp/'.$this->email.'/'.$this->OTPassword_token))
            ->line('Tienes un dia a partir de este correo para ingresar con esta contraseña');
    }

}
