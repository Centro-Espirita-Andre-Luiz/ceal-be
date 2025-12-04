<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Healer;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'patient_id'   => Patient::factory(),
            'healer_id'    => Healer::factory(),
            'appointment_id' => null,
            'checkin_time' => now(),
            'start_time'   => null,
            'end_time'     => null,
            'status'       => 'waiting',
            'queue_number' => 1,
        ];
    }
}
