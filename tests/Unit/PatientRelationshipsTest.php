<?php

namespace Tests\Unit;

use App\Models\Absence;
use App\Models\Appointment;
use App\Models\Attendance;
use App\Models\Healer;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function patient_pertence_a_um_manager()
    {
        $manager = User::factory()->create();
        $patient = Patient::factory()->create([
            'manager_id' => $manager->id,
        ]);

        $this->assertInstanceOf(User::class, $patient->manager);
        $this->assertEquals($manager->id, $patient->manager->id);
    }

    /** @test */
    public function patient_pertence_a_um_healer_preferido()
    {
        $healer = Healer::factory()->create();
        $patient = Patient::factory()->create([
            'preferred_healer_id' => $healer->id,
        ]);

        $this->assertInstanceOf(Healer::class, $patient->preferredHealer);
        $this->assertEquals($healer->id, $patient->preferredHealer->id);
    }

    /** @test */
    public function patient_pode_ter_varios_appointments()
    {
        $patient = Patient::factory()
            ->has(Appointment::factory()->count(3))
            ->create();

        $this->assertCount(3, $patient->appointments);
        $this->assertInstanceOf(Appointment::class, $patient->appointments->first());
    }

    /** @test */
    public function patient_pode_ter_varios_attendances()
    {
        $patient = Patient::factory()
            ->has(Attendance::factory()->count(2))
            ->create();

        $this->assertCount(2, $patient->attendances);
        $this->assertInstanceOf(Attendance::class, $patient->attendances->first());
    }

    /** @test */
    public function patient_pode_ter_varias_absences()
    {
        $patient = Patient::factory()
            ->has(Absence::factory()->count(2))
            ->create();

        $this->assertCount(2, $patient->absences);
        $this->assertInstanceOf(Absence::class, $patient->absences->first());
        //testCii
    }
}
