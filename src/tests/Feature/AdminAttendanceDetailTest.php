<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Status;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 必要なSeeder
        $this->seed(\Database\Seeders\StatusesTableSeeder::class);
        $this->seed(\Database\Seeders\AdminSeeder::class);
    }

    /** @test */
    public function 管理者は勤怠詳細画面の内容を確認できる()
    {
        $admin = Admin::first();
        $user  = User::factory()->create();

        $status = Status::where('name', '退勤済')->first();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $status->id,
            'remarks'   => 'テスト備考',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.detail', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('<textarea name="reason"', false);
    }

    /** @test */
    public function 出勤時間が退勤時間より後の場合はエラーになる()
    {
        $admin = Admin::first();
        $user  = User::factory()->create();
        $status = Status::where('name', '退勤済')->first();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $status->id,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->patch(route('admin.attendance.update', $attendance->id), [
                'work_in' => '19:00',
                'work_out' => '18:00',
                'remarks' => 'テスト備考',
            ]);

        $response->assertSessionHasErrors([
            'work_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の場合はエラーになる()
    {
        $admin = Admin::first();
        $user  = User::factory()->create();
        $status = Status::where('name', '退勤済')->first();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $status->id,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->patch(route('admin.attendance.update', $attendance->id), [
                'work_in' => '09:00',
                'work_out' => '18:00',
                'breaks' => [
                    ['start' => '19:00', 'end' => '19:30']
                ],
                'remarks' => 'テスト備考',
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合はエラーになる()
    {
        $admin = Admin::first();
        $user  = User::factory()->create();
        $status = Status::where('name', '退勤済')->first();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $status->id,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->patch(route('admin.attendance.update', $attendance->id), [
                'work_in' => '09:00',
                'work_out' => '18:00',
                'breaks' => [
                    ['start' => '17:00', 'end' => '19:00']
                ],
                'remarks' => 'テスト備考',
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合はエラーになる()
    {
        $admin = Admin::first();
        $user  = User::factory()->create();
        $status = Status::where('name', '退勤済')->first();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $status->id,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->patch(route('admin.attendance.update', $attendance->id), [
                'work_in' => '09:00',
                'work_out' => '18:00',
            ]);

        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
    }
}

