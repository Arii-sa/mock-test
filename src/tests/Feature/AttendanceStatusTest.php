<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // ステータスSeederを実行
        $this->seed(\Database\Seeders\StatusesTableSeeder::class);
    }

    /** @test */
    public function 勤務外の場合ステータスが正しく表示される()
    {
        $user = User::factory()->create();
        $status = Status::where('name', '勤務外')->first();

        Attendance::create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'work_date' => Carbon::today()->toDateString(),
        ]);

        $this->actingAs($user)
             ->get('/attendance')
             ->assertSee('勤務外');
    }

    /** @test */
    public function 出勤中の場合ステータスが正しく表示される()
    {
        $user = User::factory()->create();
        $status = Status::where('name', '出勤中')->first();

        Attendance::create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'work_date' => Carbon::today()->toDateString(),
            'work_in' => now()->format('H:i:s'),
        ]);

        $this->actingAs($user)
             ->get('/attendance')
             ->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中の場合ステータスが正しく表示される()
    {
        $user = User::factory()->create();
        $status = Status::where('name', '休憩中')->first();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'work_date' => Carbon::today()->toDateString(),
            'work_in' => now()->subHour()->format('H:i:s'),
        ]);

        // 休憩開始だけ BreakTime に作成
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start'   => now()->format('H:i:s'),
            'break_end'     => null,
        ]);

        $this->actingAs($user)
             ->get('/attendance')
             ->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済の場合ステータスが正しく表示される()
    {
        $user = User::factory()->create();
        $status = Status::where('name', '退勤済')->first();

        Attendance::create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'work_date' => Carbon::today()->toDateString(),
            'work_in' => now()->subHours(8)->format('H:i:s'),
            'work_out' => now()->format('H:i:s'),
        ]);

        $this->actingAs($user)
             ->get('/attendance')
             ->assertSee('退勤済');
    }
}
