<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Healer;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'healer_id' => Healer::factory(),
            'scheduled_date' => $this->faker->date(),
            'scheduled_time' => $this->faker->time(),
            'status' => 'scheduled',
        ];
    }
}
