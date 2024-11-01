<?php

namespace Tests\Unit;

use App\Http\Controllers\Auth\ValidacionEmailController;
use App\Models\Departamento;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Notifications\notificacionRegistroCorreo;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

class notificacionRegistroCorreoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_envia_el_email()
    {
        // Simula el envío de notificaciones
        Notification::fake();

        // Crea un usuario simulado
        $user = new class {
            public $id = 1;
            public $email = 'user@example.com';

            public function getKey()
            {
                return $this->id;
            }

            public function getEmailForVerification()
            {
                return $this->email;
            }
        };

        // Enviar la notificación
        Notification::send($user, new notificacionRegistroCorreo());

        // Verificar que la notificación se envió
        Notification::assertSentTo(
            [$user],
            notificacionRegistroCorreo::class
        );
    }

    /** @test */
    public function test_el_email_contiene_el_contenido_correcto()
    {
        // Simula el envío de notificaciones
        Notification::fake();

        // Crea un usuario simulado
        $user = new class {
            public $id = 1;
            public $email = 'user@example.com';

            public function getKey()
            {
                return $this->id;
            }

            public function getEmailForVerification()
            {
                return $this->email;
            }
        };

        // Enviar la notificación
        Notification::send($user, new notificacionRegistroCorreo());

        // Obtener la notificación enviada
        $notification = Notification::sent($user, notificacionRegistroCorreo::class)->first();

        // Verificar el contenido del correo
        $this->assertEquals('Expedientes ITO - Verifica tu correo electrónico', $notification->toMail($user)->subject);
        $this->assertStringContainsString('Se te ha creado una cuenta en la plataforma de Expedientes ITO', $notification->toMail($user)->render());
        $this->assertStringContainsString('Haz clic en el botón de abajo para verificar tu dirección de correo electrónico.', $notification->toMail($user)->render());
        $this->assertStringContainsString('Si no creaste una cuenta, no se requiere ninguna otra acción.', $notification->toMail($user)->render());
    }

    public function test_redirecciona_si_el_usuario_esta_verificado()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => Hash::make('passworD@7'),
            'IdPersonal' => $personal->IdPersonal,
            'email_verified_at' => now(),
        ]);

        // Simula la autenticación
        Auth::loginUsingId($user->id);

        // Llama al método __invoke
        $controller = new ValidacionEmailController();
        $response = $controller->__invoke($user->id);

        // Asegúrate de que la redirección sea a la ruta 'dashboard'
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('dashboard'), $response->getTargetUrl());
    }

    public function test_verifica_al_usaurio_y_dispara_el_evento()
    {
        // Desactivar eventos para asegurarnos de que se dispara el evento correcto
        Event::fake();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => Hash::make('passworD@7'),
            'IdPersonal' => $personal->IdPersonal,
            'email_verified_at' => null,
        ]);

        // Simula la autenticación
        Auth::loginUsingId($user->id);

        // Espera que se dispare el evento Verified
        Event::fake();

        // Llama al método __invoke
        $controller = new ValidacionEmailController();
        $response = $controller->__invoke($user->id);

        // Asegúrate de que el correo se marca como verificado
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        // Verifica que el evento Verified fue disparado
        Event::assertDispatched(Verified::class, function ($event) use ($user) {
            return $event->user->is($user);
        });

        // Asegúrate de que la redirección sea a la ruta 'dashboard'
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('dashboard'), $response->getTargetUrl());
    }
}
