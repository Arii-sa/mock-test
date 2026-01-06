<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;
use App\Models\Attendance;
use Carbon\Carbon;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition()
    {
        $attendance = Attendance::inRandomOrder()->first();

        $workIn = Carbon::parse($attendance->work_in);

        $start = $workIn->copy()->addMinutes(rand(60, 180));
        $end = $start->copy()->addMinutes(rand(30, 90));

        return [
            'attendance_id' => $attendance->id,
            'break_start' => $start,
            'break_end' => $end,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
