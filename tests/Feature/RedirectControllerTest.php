<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_redireciona_usuario_autenticado_para_ceal()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/');

        $response->assertRedirect('/ceal');
    }

    /** @test */
    public function index_redireciona_usuario_nao_autenticado_para_login()
    {
        $response = $this->get('/');

        $response->assertRedirect('/ceal/login');
    }
}
