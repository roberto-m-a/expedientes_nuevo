<?php

namespace Tests\Unit;

use App\Notifications\ContraseniaTemporal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\AnonymousNotifiable;

class ContraseniaTemporalTest extends TestCase
{
    use RefreshDatabase;
    public function test_contrasenia_temporal_se_envia_por_correo()
    {
        // Simula las notificaciones
        Notification::fake();

        // Datos de prueba
        $email = 'usuario@example.com';
        $OTPassword = '123456';
        $OTPasswordToken = 'token123';

        // Crear una instancia de la notificación
        $notification = new ContraseniaTemporal($email, $OTPassword, $OTPasswordToken);

        // Simular envío de la notificación
        Notification::route('mail', $email)->notify($notification);

        // Verificar que la notificación fue enviada a través del canal de correo
        Notification::assertSentTo(
            new AnonymousNotifiable,
            ContraseniaTemporal::class,
            function ($notification, $channels) use ($email, $OTPassword, $OTPasswordToken) {
                return in_array('mail', $channels);
            }
        );
    }

    public function test_contenido_correo_contrasenia_temporal()
    {
        // Datos de prueba
        $email = 'usuario@example.com';
        $OTPassword = '123456';
        $OTPasswordToken = 'token123';

        // Crear una instancia de la notificación
        $notification = new ContraseniaTemporal($email, $OTPassword, $OTPasswordToken);

        // Obtener el mensaje de correo generado
        $mailData = $notification->toMail(new AnonymousNotifiable);

        // Verificar que el asunto es correcto
        $this->assertEquals('Expedientes ITO - Contraseña temporal', $mailData->subject);

        // Verificar que el cuerpo del mensaje contiene las líneas esperadas
        $this->assertStringContainsString('Ingresa con la siguiente contraseña temporal', $mailData->introLines[0]);
        $this->assertStringContainsString($OTPassword, $mailData->introLines[1]);

        // Verificar que el enlace de acción es correcto
        $expectedUrl = url('/verificar-otp/' . $email . '/' . $OTPasswordToken);
        $this->assertEquals($expectedUrl, $mailData->actionUrl);

        // Verificar que la línea final del correo es correcta
        $this->assertStringContainsString('Tienes un dia a partir de este correo para ingresar con esta contraseña', $mailData->outroLines[0]);
    }
}
