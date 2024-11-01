<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use Mockery;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AppServiceProviderTest extends TestCase
{
    /**
     * Prueba para verificar que se fuerza el esquema https en producción.
     *
     * @return void
     */
    public function test_fuerza_el_esquema_https_cuando_esta_en_produccion()
    {
        // Simulamos que estamos en el entorno de producción.
        config(['app.env' => 'production']);

        // Mockear el facade URL.
        URL::shouldReceive('forceScheme')
            ->once()
            ->with('https');

        // Ejecutamos el método boot del AppServiceProvider.
        $provider =new AppServiceProvider($this->app);
        $provider->boot();
    }

    /**
     * Prueba para verificar que no se forza https cuando no estamos en producción.
     *
     * @return void
     */
    public function test_no_fuerza_el_esquema_cuando_no_esta_en_produccion()
    {
        // Simulamos que estamos en un entorno que no es de producción.
        config(['app.env' => 'local']);

        // Aseguramos que el método forceScheme no es llamado.
        URL::shouldReceive('forceScheme')
            ->never();

        // Ejecutamos el método boot del AppServiceProvider.
        $provider = new AppServiceProvider($this->app);
        $provider->boot();
    }
}
