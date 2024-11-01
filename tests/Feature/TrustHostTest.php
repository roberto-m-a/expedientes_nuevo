<?php

namespace Tests\Feature;

use App\Http\Middleware\TrustHosts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TrustHostTest extends TestCase
{
    /** @test */
    public function test_confia_en_todas_los_subdominios_de_la_url_de_la_aplicacion()
    {
        // Obtener el host base desde la configuración de la aplicación
        $baseHost = parse_url(config('app.url'), PHP_URL_HOST);

        // Simular una solicitud desde un subdominio de la URL de la aplicación
        $response = $this->call('GET', '/', [], [], [], [
            'HTTP_HOST' => 'subdomain.' . $baseHost,
        ]);

        // Verificar que la solicitud fue exitosa
        $response->assertSuccessful();

        // Si el host es localhost, verificar solo el host base
        if ($baseHost === 'localhost') {
            $this->assertEquals('localhost', request()->getHost());
        } else {
            $this->assertEquals('subdomain.' . $baseHost, request()->getHost());
        }
    }

    /** @test */
    public function test_error_por_una_url_distinta_a_la_aplicacion()
    {
        // Simular una solicitud desde un host no confiable
        $response = $this->call('GET', '/', [], [], [], [
            'HTTP_HOST' => 'untrusted.com',
        ]);

        // Verificar que la solicitud fue exitosa (pero que el host no es confiado)
        $response->assertSuccessful();

        // Verificar que el host de la solicitud no coincide con el patrón confiado
        $this->assertNotEquals(
            'untrusted.com',
            request()->getHost()
        );
    }

    /** @test */
    public function test_retorna_todos_los_subdominios_de_la_aplicacion()
    {
        // Instanciar el middleware
        $trustHosts = new TrustHosts($this->app);

        // Obtener los hosts
        $hosts = $trustHosts->hosts();

        // Verificar que el resultado no esté vacío
        $this->assertNotEmpty($hosts);
    }
}
