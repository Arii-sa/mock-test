<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceCorrectionBreak;
use App\Models\AttendanceCorrection;
use Carbon\Carbon;

class AttendanceCorrectionBreakFactory extends Factory
{
    protected $model = AttendanceCorrectionBreak::class;

    public function definition()
    {
        $correction = AttendanceCorrection::inRandomOrder()->first();
        $start = now()->subDays(rand(1, 30))->setTime(rand(10, 12), 0);
        $end = (clone $start)->modify('+'.rand(30, 120).' minutes');

        return [
            'correction_id' => $correction->id,
            'break_start' => $start,
            'break_end' => $end,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
