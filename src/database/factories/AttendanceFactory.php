<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\Status;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        $workDate = $this->faker->dateTimeBetween('-1 month', 'now');

        $workIn = (clone $workDate)->setTime(rand(8, 10), rand(0, 59)); // 出勤 8:00〜10:59
        $workOut = (clone $workDate)->setTime(rand(17, 20), rand(0, 59)); // 退勤 17:00〜20:59

        return [
            'status_id' => Status::inRandomOrder()->first()->id,
            'work_date' => $workDate->format('Y-m-d'),
            'work_in' => $workIn,
            'work_out' => $workOut,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

