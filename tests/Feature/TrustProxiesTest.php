<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TrustProxiesTest extends TestCase
{
    /** @test */
    public function test_encabezados_procesados_correctamente()
    {
        config(['trustedproxy.proxies' => '*']); // Confía en todas las IPs

        // Simular una solicitud a través de un proxy
        $response = $this->call('GET', '/', [], [], [], [
            'HTTP_X_FORWARDED_FOR' => '192.168.10.10',
            'HTTP_X_FORWARDED_HOST' => 'proxy.example.com',
            'HTTP_X_FORWARDED_PORT' => '443',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ]);

        // Verificar que los encabezados fueron detectados correctamente
        $response->assertSuccessful();

        // Verificar que el middleware TrustProxies procesó los encabezados
        $this->assertEquals('192.168.10.10', request()->ip());
        $this->assertEquals('https', request()->getScheme());
        $this->assertEquals('443', request()->getPort());
        $this->assertEquals('proxy.example.com', request()->getHost());
    }

    /** @test */
    public function test_encabezados_no_utilizados()
    {
        // Simular una solicitud SIN pasar por un proxy
        $response = $this->call('GET', '/');

        // Verificar que los encabezados no fueron utilizados
        $response->assertSuccessful();
        $this->assertNotEquals('192.168.10.10', request()->ip());
    }
}
