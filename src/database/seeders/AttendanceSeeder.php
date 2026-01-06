<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceCorrectionBreak;
use App\Models\BreakTime;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();

        if (!$user) {
            $this->command->info('一般ユーザーが存在しません。Seederを終了します。');
            return;
        }

        $attendances = Attendance::factory()->count(20)->create([
            'user_id' => $user->id,
        ]);


        $corrections = $attendances->random(3)->map(function($attendance) use ($user) {
            return AttendanceCorrection::factory()->create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'reason' => '勤務時間の修正申請です。内容を確認してください。',
            ]);
        });


        for ($i = 0; $i < 5; $i++) {
            AttendanceCorrectionBreak::factory()->create([
                'correction_id' => $corrections->random()->id,
            ]);
        }

        for ($i = 0; $i < 20; $i++) {
            BreakTime::factory()->create([
                'attendance_id' => $attendances->random()->id,
            ]);
        }
    }
}

