<?php

namespace Tests\Unit;

use App\Models\Healer;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealerRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function healer_pertence_a_um_user()
    {
        $user = User::factory()->create();

        $healer = Healer::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $healer->user);
        $this->assertEquals($user->id, $healer->user->id);
    }

    /** @test */
    public function healer_pode_ter_varios_pacientes()
    {
        $healer = Healer::factory()
            ->has(Patient::factory()->count(3), 'patients')
            ->create();

        $this->assertCount(3, $healer->patients);
        $this->assertInstanceOf(Patient::class, $healer->patients->first());
    }
}
