<?php

namespace Database\Factories;

use App\Models\Absence;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AbsenceFactory extends Factory
{
    protected $model = Absence::class;

    public function definition(): array
    {
        return [
            'patient_id'     => Patient::factory(),
            'appointment_id' => Appointment::factory(),
            'absence_date'   => $this->faker->date(),  // campo NOT NULL na migration
            'justified'      => false,
            'approved_by'    => User::factory(),
        ];
    }
}
