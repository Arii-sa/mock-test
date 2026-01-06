<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Status;
use App\Models\ApplicationStatus;
use App\Models\AttendanceCorrection;
use Carbon\Carbon;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 外部キーで必須なSeeder
        $this->seed(\Database\Seeders\StatusesTableSeeder::class);
        $this->seed(\Database\Seeders\ApplicationStatusesTableSeeder::class);
        $this->seed(\Database\Seeders\AdminSeeder::class);
    }

    /** @test */
    public function 出勤時間が退勤時間より後の場合エラーになる()
    {
        $user = User::factory()->create();

        $status = Status::where('name', '退勤済')->first();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $status->id,
        ]);

        $response = $this->actingAs($user)->post(
            route('attendance.request', $attendance->id),
            [
                'work_in'  => '19:00',
                'work_out' => '18:00',
                'reason'   => 'テスト',
            ]
        );

        $response->assertSessionHasErrors([
            'work_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の場合エラーになる()
    {
        $user = User::factory()->create();

        $status = Status::where('name', '退勤済')->first();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $status->id,
        ]);


        $response = $this->actingAs($user)->post(
            route('attendance.request', $attendance->id),
            [
                'work_in'  => '09:00',
                'work_out' => '18:00',
                'breaks' => [
                    [
                        'start' => '19:00',
                        'end'   => '19:30',
                    ],
                ],
                'reason' => 'テスト',
            ]
        );

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合エラーになる()
    {
        $user = User::factory()->create();

        $status = Status::where('name', '退勤済')->first();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $status->id,
        ]);

        $response = $this->actingAs($user)->post(
            route('attendance.request', $attendance->id),
            [
                'work_in'  => '09:00',
                'work_out' => '18:00',
                'breaks' => [
                    [
                        'start' => '17:00',
                        'end'   => '19:00',
                    ],
                ],
                'reason' => 'テスト',
            ]
        );

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 備考未入力の場合エラーになる()
    {
        $user = User::factory()->create();

        $status = Status::where('name', '退勤済')->first();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $status->id,
        ]);

        $response = $this->actingAs($user)->post(
            route('attendance.request', $attendance->id),
            [
                'work_in'  => '09:00',
                'work_out' => '18:00',
            ]
        );

        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
    }

    /** @test */
    public function 修正申請が正常に作成される()
    {
        $user = User::factory()->create();

        $status = Status::where('name', '退勤済')->first();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $status->id,
        ]);

        $this->actingAs($user)->post(
            route('attendance.request', $attendance->id),
            [
                'work_in'  => '09:00',
                'work_out' => '18:00',
                'reason'   => '修正申請テスト',
            ]
        );

        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'reason'        => '修正申請テスト',
        ]);
    }

    /** @test */
    public function 申請一覧に自分の申請が表示される()
    {
        $user = User::factory()->create();

        $status = Status::where('name', '退勤済')->first();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $status->id,
        ]);

        $pendingStatus = ApplicationStatus::where('name', '承認待ち')->first();

        AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'applications_status_id' => $pendingStatus->id,
            'reason' => '一覧表示テスト',
        ]);

        $this->actingAs($user)
            ->get(route('attendance_correction.list'))
            ->assertStatus(200)
            ->assertSee('一覧表示テスト');
    }

    /** @test */
    public function 承認済みに管理者が承認した修正申請が全て表示されている()
    {
        // 一般ユーザー
        $user = User::factory()->create();

        $adminUser = User::factory()->create([
            'name' => '管理者役ユーザー',
            'email' => 'admin_test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 勤怠データ
        $status = Status::where('name', '退勤済')->first();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $status->id,
        ]);

        $approvedStatus = ApplicationStatus::where('name', '承認済み')->firstOrFail();
        // 修正申請（承認済み）
        $approvedCorrection = AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'applications_status_id' => $approvedStatus->id,
            'reason' => '承認済みテスト',
            'approved_at' => now(),
            'approved_by' => $adminUser->id,
        ]);

        // 申請一覧（承認済みタブ）を確認
        $this->actingAs($user)
            ->get(route('attendance_correction.list', ['tab' => 'approved']))
            ->assertStatus(200)
            ->assertSee('承認済みテスト');
    }


    /** @test */
    public function 申請詳細ボタンから勤怠詳細画面に遷移できる()
    {
        $user = User::factory()->create();

        $status = Status::where('name', '退勤済')->first();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $status->id,
        ]);

        $approvedStatus = ApplicationStatus::where('name', '承認済み')->firstOrFail();

        $correction = AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'applications_status_id' => $approvedStatus->id,
            'reason' => '詳細遷移テスト',
        ]);

        $this->actingAs($user)
            ->get(route('attendance.detail', $attendance->id))
            ->assertStatus(200)
            ->assertSee('詳細遷移テスト');
    }
}
