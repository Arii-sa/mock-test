<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Status;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected $statusId;

    protected function setUp(): void
    {
        parent::setUp();

        // 必要なステータスを直接作成
        $this->statusId = \DB::table('statuses')->insertGetId([
            'name'       => '退勤済',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test */
    public function 勤怠詳細画面にログインユーザーの名前が表示される()
    {
        // ユーザー作成
        $user = User::factory()->create([
            'name' => '山田 太郎',
        ]);

        // 勤怠データ作成
        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $this->statusId,
        ]);

        $this->actingAs($user)
            ->get('/attendance/detail/2026-01-10')
            ->assertStatus(200)
            ->assertSee('山田 太郎');
    }

    /** @test */
    public function 勤怠詳細画面に選択した日付が表示される()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $this->statusId,
        ]);

        $this->actingAs($user)
            ->get('/attendance/detail/2026-01-10')
            ->assertStatus(200)
            ->assertSee('2026年1月10日');
    }

    /** @test */
    public function 出勤退勤時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $this->statusId,
        ]);

        $this->actingAs($user)
            ->get('/attendance/detail/2026-01-10')
            ->assertStatus(200)
            ->assertSee('09:00')
            ->assertSee('18:00');
    }

    /** @test */
    public function 休憩時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $this->statusId,
        ]);

        // 休憩データ作成
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start'    => '12:00:00',
            'break_end'      => '13:00:00',
        ]);

        // 休憩時間の合計1時間が表示されることを確認
        $this->actingAs($user)
            ->get('/attendance/detail/2026-01-10')
            ->assertStatus(200)
            ->assertSee('12:00')
            ->assertSee('13:00');
    }
}
