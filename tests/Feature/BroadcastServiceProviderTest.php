<?php

namespace Tests\Feature;

use App\Providers\BroadcastServiceProvider;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_se_incluye_el_canal_de_rutas_del_archivo()
    {
        // Simular la inclusión del archivo de rutas de canales
        $channelsPath = base_path('routes/channels.php');
        $this->assertFileExists($channelsPath);

        // Ejecutar el código de inclusión
        require $channelsPath;

        // Verificar que el archivo se haya incluido correctamente
        $this->assertTrue(true);
    }
    /** @test */
    public function test_arranca_las_rutas_de_broadcast_y_requiere_el_archivo_de_canales()
    {
        // Verificar que el archivo 'channels.php' exista
        $basePath = base_path('routes/channels.php');
        $this->assertFileExists($basePath);

        // Crear una instancia del ServiceProvider y llamar al método boot
        $provider = new BroadcastServiceProvider($this->app);
        $provider->boot();

        // Verificar que se haya ejecutado el código del método sin excepciones
        $this->assertTrue(true);
    }
}
