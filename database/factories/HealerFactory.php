<?php

namespace Database\Factories;

use App\Models\Healer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HealerFactory extends Factory
{
    protected $model = Healer::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'specialty' => $this->faker->word(),
            'active' => true,
            'bio' => $this->faker->sentence(),
        ];
    }
}
