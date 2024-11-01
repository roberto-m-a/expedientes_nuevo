<?php

namespace Tests\Feature\Auth;

use App\Models\Departamento;
use App\Models\Personal;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_vista_de_verificacion_de_email_se_renderiza_correctamente(): void
    {
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

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_el_email_puede_ser_verificado(): void
    {
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

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(RouteServiceProvider::HOME.'?verified=1');
    }

    public function test_el_email_no_se_verifica_por_token_y_correo_incorrectos(): void
    {
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

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    /** @test */
    public function test_redirecciona_al_inicio_por_estar_verificado()
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

        // Act: Act as the verified user and hit the store route
        $response = $this->actingAs($user)->post(route('verification.send'));

        // Assert: User is redirected to home
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    /** @test */
    public function test_envia_el_correo_de_verificacion_por_no_estar_verificado()
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
            'email_verified_at' => null,
        ]);
        Notification::fake(); // Fake notifications to verify later

        // Act: Act as the unverified user and hit the store route
        $response = $this->actingAs($user)->post(route('verification.send'));

        // Assert: Check that the verification notification was sent
        Notification::assertSentTo($user, VerifyEmail::class);

        // Assert: User is redirected back with a status message
        $response->assertRedirect();
        $response->assertSessionHas('status', 'verification-link-sent');
    }
}
