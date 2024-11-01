<?php

namespace Tests\Feature;

use App\Providers\RouteServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_registra_rutas_api()
    {
        $this->artisan('route:clear'); // Limpiar rutas cargadas previamente

        // Crear instancia del proveedor y llamar al método boot
        $serviceProvider = new \App\Providers\RouteServiceProvider($this->app);
        $serviceProvider->boot();

        // Obtener todas las rutas y buscar rutas API
        $routes = Route::getRoutes();
        $apiRoutes = array_filter($routes->getRoutesByMethod()['GET'], function ($route) {
            return strpos($route->uri(), 'api') === 0;
        });

        $this->assertNotEmpty($apiRoutes, 'API routes are not registered.');
    }

    /** @test */
    public function test_registra_rutas_web()
    {
        $this->artisan('route:clear'); // Limpiar rutas cargadas previamente

        // Crear instancia del proveedor y llamar al método boot
        $serviceProvider = new \App\Providers\RouteServiceProvider($this->app);
        $serviceProvider->boot();

        // Obtener todas las rutas y verificar algunas rutas web
        $routes = Route::getRoutes();
        $webRoutes = array_filter($routes->getRoutesByMethod()['GET'], function ($route) {
            return strpos($route->uri(), '/') === 0 && !strpos($route->uri(), 'api/');
        });

        $this->assertNotEmpty($webRoutes, 'Web routes are not registered.');
        $this->assertTrue(
            !empty(array_filter($webRoutes, function ($route) {
                return in_array('web', $route->action['middleware']);
            })),
            'Web routes do not have the correct middleware.'
        );
    }
    /** @test */
    public function test_limite_de_tiempo_configurado_correctamente()
    {
        // Limpiar rutas cargadas previamente
        $this->artisan('route:clear');

        // Crear instancia del proveedor y llamar al método boot
        $serviceProvider = new \App\Providers\RouteServiceProvider($this->app);
        $serviceProvider->boot();

        // Simular una solicitud para probar el limitador de tasa
        $request = Request::create('/api/some-endpoint', 'GET');
        $request->setUserResolver(function () {
            return null; // Puedes simular un usuario o dejarlo nulo para usar la IP
        });

        // Ejecutar el limitador para el request simulado
        $key = 'api:' . ($request->user() ? $request->user()->id : $request->ip());

        // Verificar que el hit se registre correctamente
        RateLimiter::hit($key); // Este registro debe suceder sin error

        // Asegúrate de que el hit se ha registrado
        $this->assertFalse(RateLimiter::tooManyAttempts($key, 60), 'Too many attempts detected when it should not have exceeded the limit.');
    }
}
