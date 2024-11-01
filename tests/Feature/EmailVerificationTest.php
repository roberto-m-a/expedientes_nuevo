<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Models\Departamento;
use App\Models\Personal;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirecciona_al_inicio_si_el_correo_ya_esta_verificado()
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

        // Simular la autenticación del usuario
        $this->actingAs($user);

        // Llamar al método store del controlador
        $response = $this->post(route('verification.send'));

        // Asegurar que redirige a la ruta HOME
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_envia_el_correo_de_verificacion_si_no_esta_verificado()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Desactivar las notificaciones
        Notification::fake();

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

        // Simular la autenticación del usuario
        $this->actingAs($user);

        // Llamar al método store del controlador
        $response = $this->post(route('verification.send'));

        // Verificar que la notificación se envió
        Notification::assertSentTo($user, VerifyEmail::class);

        // Asegurar que se redirige de vuelta con un mensaje de estado
        $response->assertSessionHas('status', 'verification-link-sent');
    }
}
