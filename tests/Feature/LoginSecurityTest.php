<?php

namespace Tests\Feature;

use App\Models\ConfiguracionSistema;
use App\Models\User;
use App\Services\LoginSecurityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class LoginSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\ConfiguracionSeeder::class);
    }

    public function test_bloquea_tras_cinco_intentos_fallidos(): void
    {
        $service = app(LoginSecurityService::class);
        $request = Request::create('/login', 'POST');

        for ($i = 0; $i < 5; $i++) {
            $service->registrarIntento('test@example.com', $request, false);
        }

        $this->assertTrue($service->estaBloqueado('test@example.com'));
    }

    public function test_login_exitoso_redirige_al_dashboard(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
    }
}
