<?php

namespace Database\Factories;

use App\Models\Healer;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'birth_date' => $this->faker->date(),
            'access_code' => strtoupper($this->faker->bothify('???###')),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'manager_id' => User::factory(),
            'preferred_healer_id' => Healer::factory(),
        ];
    }
}
