<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Status;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // ステータスSeeder実行
        $this->seed(\Database\Seeders\StatusesTableSeeder::class);
    }

    /** @test */
    public function 休憩入ボタンが表示され_処理後ステータスが休憩中になる()
    {
        $user = User::factory()->create();

        // 出勤中の勤怠を作成
        Attendance::create([
            'user_id' => $user->id,
            'status_id' => Status::where('name', '出勤中')->value('id'),
            'work_date' => Carbon::today(),
            'work_in' => Carbon::now(),
        ]);

        // 休憩入ボタン表示確認
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩入');

        // 休憩入処理
        $this->post('/attendance/break-in')
            ->assertRedirect('/attendance');

        // ステータスが休憩中になっている
        $this->get('/attendance')
            ->assertSee('休憩中');
    }

    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'status_id' => Status::where('name', '出勤中')->value('id'),
            'work_date' => Carbon::today(),
            'work_in' => Carbon::now(),
        ]);

        // 休憩入 → 休憩戻
        $this->actingAs($user)->post('/attendance/break-in');
        $this->actingAs($user)->post('/attendance/break-out');

        // 再度 休憩入ボタンが表示される
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩入');
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能し_ステータスが出勤中に戻る()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'status_id' => Status::where('name', '出勤中')->value('id'),
            'work_date' => Carbon::today(),
            'work_in' => Carbon::now(),
        ]);

        // 休憩入
        $this->actingAs($user)->post('/attendance/break-in');

        // 休憩戻ボタン表示確認
        $this->get('/attendance')
            ->assertSee('休憩戻');

        // 休憩戻処理
        $this->post('/attendance/break-out')
            ->assertRedirect('/attendance');

        // 出勤中に戻る
        $this->get('/attendance')
            ->assertSee('出勤中');
    }

    /** @test */
    public function 休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'status_id' => Status::where('name', '出勤中')->value('id'),
            'work_date' => Carbon::today(),
            'work_in' => Carbon::now(),
        ]);

        // 休憩入 → 休憩戻 → 休憩入
        $this->actingAs($user)->post('/attendance/break-in');
        $this->actingAs($user)->post('/attendance/break-out');
        $this->actingAs($user)->post('/attendance/break-in');

        // 休憩戻ボタンが表示される
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'status_id' => Status::where('name', '出勤中')->value('id'),
            'work_date' => Carbon::today(),
            'work_in' => Carbon::now(),
        ]);

        // 休憩入 → 休憩戻
        $this->actingAs($user)->post('/attendance/break-in');
        sleep(1); // 時刻差を作る
        $this->actingAs($user)->post('/attendance/break-out');

        $break = BreakTime::whereHas('attendance', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->latest()->first();

        $this->assertNotNull($break);
        $this->assertNotNull($break->break_start);
        $this->assertNotNull($break->break_end);

        // 勤怠一覧画面に表示される
        $this->actingAs($user)
            ->get('/attendance/list')
            ->assertSee(Carbon::parse($break->break_start)->format('H:i'))
            ->assertSee(Carbon::parse($break->break_end)->format('H:i'));
    }
}

