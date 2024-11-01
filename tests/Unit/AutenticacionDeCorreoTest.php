<?php

namespace Tests\Unit;

use App\Notifications\AutenticacionDeCorreo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\AnonymousNotifiable;

class AutenticacionDeCorreoTest extends TestCase
{
    use RefreshDatabase;
    public function test_autenticacion_de_correo_se_envia_por_correo()
    {
        // Simula las notificaciones
        Notification::fake();

        // Datos de prueba
        $email = 'usuario@example.com';
        $token = 'token123';

        // Crear una instancia de la notificación
        $notification = new AutenticacionDeCorreo($email, $token);

        // Simular envío de la notificación
        Notification::route('mail', $email)->notify($notification);

        // Verificar que la notificación fue enviada a través del canal de correo
        Notification::assertSentTo(
            new AnonymousNotifiable,
            AutenticacionDeCorreo::class,
            function ($notification, $channels) use ($email, $token) {
                return in_array('mail', $channels);
            }
        );
    }

    public function test_contenido_correo_autenticacion_de_correo()
    {
        // Datos de prueba
        $email = 'usuario@example.com';
        $token = 'token123';

        // Crear una instancia de la notificación
        $notification = new AutenticacionDeCorreo($email, $token);

        // Obtener el mensaje de correo generado
        $mailData = $notification->toMail(new AnonymousNotifiable);

        // Verificar que el asunto es correcto
        $this->assertEquals('Expedientes ITO - Verifica tu correo electrónico', $mailData->subject);

        // Verificar que el cuerpo del mensaje contiene las líneas esperadas
        $this->assertStringContainsString('Se te ha creado una cuenta en la plataforma de Expedientes ITO', $mailData->introLines[0]);
        $this->assertStringContainsString('Haz clic en el botón de abajo para verificar tu dirección de correo electrónico.', $mailData->introLines[1]);

        // Verificar que el enlace de acción es correcto
        $expectedUrl = url('/verificar-correo/'.$email.'/'.$token);
        $this->assertEquals($expectedUrl, $mailData->actionUrl);

        // Verificar que la línea final del correo es correcta
        $this->assertStringContainsString('Tienes un día a partir de la fecha de este correo para verificarlo', $mailData->outroLines[0]);
    }
}
