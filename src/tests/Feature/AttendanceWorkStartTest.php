<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Status;
use Carbon\Carbon;

class AttendanceWorkStartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // ステータスSeederを実行
        $this->seed(\Database\Seeders\StatusesTableSeeder::class);
    }

    /** @test */
    public function 出勤ボタンが表示され、出勤処理後ステータスが勤務中になる()
    {
        $user = User::factory()->create();
        $status = Status::where('name', '勤務外')->first();

        $this->actingAs($user)
            ->get('/attendance')
            ->assertStatus(200)
            ->assertSee('出勤'); // 出勤ボタンが表示されているか確認

        // 出勤処理
        $response = $this->post('/attendance/start');

        $response->assertRedirect('/attendance');

        $this->get('/attendance')
            ->assertSee('出勤中'); // 出勤後のステータス
    }

    /** @test */
    public function 出勤は一日一回のみ()
    {
        $user = User::factory()->create();
        $status = Status::where('name', '退勤済')->first();

        // 退勤済の勤怠を作成
        Attendance::create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'work_date' => Carbon::now()->toDateString(),
            'work_in' => Carbon::now()->subHours(8),
            'work_out' => Carbon::now(),
        ]);

        $this->actingAs($user)
            ->get('/attendance')
            ->assertStatus(200)
            ->assertDontSee('出勤'); // 出勤ボタンは表示されない
    }

    /** @test */
    public function 出勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();

        // 出勤処理
        $this->actingAs($user)
            ->post('/attendance/start')
            ->assertRedirect('/attendance');

        // DBに保存されていることを確認（←ここ重要）
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
        ]);

        // 最新の勤怠を取得（whereDate を使わない）
        $attendance = Attendance::where('user_id', $user->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->work_in);

        // 勤怠一覧画面に出勤時刻が表示されているか
        $this->actingAs($user)
            ->get('/attendance/list')
            ->assertSee(Carbon::parse($attendance->work_in)->format('H:i'));
    }

}

