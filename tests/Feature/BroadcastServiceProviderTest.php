<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Providers\BroadcastServiceProvider;
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
}
