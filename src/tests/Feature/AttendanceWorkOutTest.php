<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Status;
use Carbon\Carbon;

class AttendanceWorkOutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // ステータスマスタを投入
        $this->seed(\Database\Seeders\StatusesTableSeeder::class);
    }

    /** @test */
    public function 退勤ボタンが表示され_退勤処理後ステータスが退勤済になる()
    {
        $user = User::factory()->create();

        // 出勤中の勤怠を作成
        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'work_in'   => Carbon::now()->subHours(8),
            'status_id' => Status::where('name', '出勤中')->value('id'),
        ]);

        // 勤怠画面を表示
        $this->actingAs($user)
            ->get('/attendance')
            ->assertStatus(200)
            ->assertSee('退勤');

        // 退勤処理
        $this->actingAs($user)
            ->post('/attendance/leave')
            ->assertRedirect('/attendance');

        // ステータスが「退勤済」になっている
        $this->get('/attendance')
            ->assertSee('退勤済');
    }

    /** @test */
    public function 退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();

        // 出勤
        $this->actingAs($user)
            ->post('/attendance/start')
            ->assertRedirect('/attendance');

        // 退勤
        $this->actingAs($user)
            ->post('/attendance/leave')
            ->assertRedirect('/attendance');

        // DB確認
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', Carbon::today())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->work_out);

        // 勤怠一覧画面に退勤時刻が表示されている
        $this->actingAs($user)
            ->get('/attendance/list')
            ->assertSee(Carbon::parse($attendance->work_out)->format('H:i'));
    }
}

