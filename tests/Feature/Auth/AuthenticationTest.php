<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Departamento;
use App\Models\Personal;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_vista_de_login_se_renderiza_correctamente(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test__los_usuarios_pueden_autenticarse_con_el_login(): void
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
/* 
        $response = Request::create('/login', 'POST', [
            'email' => $user->email,
            'password' => 'passworD@7',
        ]); */
        $response = $this->post(route('login'),[
            'email' => 'test@itoaxaca.edu.mx',
            'password' => 'passworD@7',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_no_se_puede_autenticar_con_una_contraseña_incorrecta(): void
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
            'email_verified_at' => now(),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_los_usuarios_pueden_cerrar_sesion(): void
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
        $this->actingAs($user);
        $response = $this->post('/logout');

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

}
