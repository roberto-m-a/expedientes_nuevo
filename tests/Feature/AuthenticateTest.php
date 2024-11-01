<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_redirecciona_al_login_si_el_usaurio_no_esta_autenticado_y_no_es_un_json()
    {
        // Simular una solicitud a una ruta protegida que no espera JSON
        $response = $this->get('/dashboard');

        // Verificar que el usuario es redirigido a la ruta de login
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function test_redirecciona_al_login_si_el_usaurio_no_esta_autenticado_y_es_un_json()
    {
        // Simular una solicitud a una ruta protegida que espera una respuesta JSON
        $response = $this->getJson('/dashboard');

        // Verificar que no hay redirección y el estado es 401 Unauthorized
        $response->assertStatus(401);
    }
}
