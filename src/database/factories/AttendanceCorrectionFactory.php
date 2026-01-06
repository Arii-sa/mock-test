<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceCorrection;
use App\Models\Attendance;
use App\Models\User;
use App\Models\ApplicationStatus;
use Carbon\Carbon;

class AttendanceCorrectionFactory extends Factory
{
    protected $model = AttendanceCorrection::class;

    public function definition()
    {
        $attendance = Attendance::inRandomOrder()->first();
        $user = $attendance ? $attendance->user : User::first();

        return [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'applications_status_id' => ApplicationStatus::inRandomOrder()->first()->id,
            'reason' => '',
            'request_start_time' => $attendance->work_in,
            'request_end_time' => $attendance->work_out,
            'approved_at' => null,
            'approved_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

