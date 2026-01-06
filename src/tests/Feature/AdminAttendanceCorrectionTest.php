<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakTime;

class AdminAttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者作成
        $this->admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // 一般ユーザー作成
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        // 外部キー用データ
        DB::table('statuses')->insert([
            ['id' => 1, 'name' => '出勤済み', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => '欠勤', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('applications_statuses')->insert([
            ['id' => 1, 'name' => '承認待ち', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => '承認済み', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /** @test */
    public function 承認待ちの修正申請が全て表示されている()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-01-01',
            'work_in' => '09:00',
            'work_out' => '18:00',
            'status_id' => 1,
        ]);

        AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $this->user->id,
            'applications_status_id' => 1,
            'reason' => '承認待ち理由',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);
        $response->assertSee('承認待ち理由');
    }

    /** @test */
    public function 承認済みの修正申請が全て表示されている()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-01-02',
            'work_in' => '09:00',
            'work_out' => '18:00',
            'status_id' => 1,
        ]);

        AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $this->user->id,
            'applications_status_id' => 2,
            'reason' => '承認済み理由',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み理由');
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示されている()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-01-03',
            'work_in' => '09:00',
            'work_out' => '18:00',
            'status_id' => 1,
        ]);

        $correction = AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $this->user->id,
            'request_start_time' => '10:00',
            'request_end_time' => '19:00',
            'reason' => '詳細確認用理由',
            'applications_status_id' => 1,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get("/admin/stamp_correction_request/detail/{$correction->id}");

        $response->assertStatus(200);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('詳細確認用理由');
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
    {
        // Attendance 作成
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-01-04',
            'work_in' => '09:00',
            'work_out' => '18:00',
            'status_id' => 1,
        ]);

        // AttendanceCorrection 作成
        $correction = AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $this->user->id,
            'request_start_time' => '10:00',
            'request_end_time' => '19:00',
            'applications_status_id' => 1,
            'reason' => '承認処理用理由',
        ]);

        // 修正申請に紐づく休憩を作成
        \App\Models\AttendanceCorrectionBreak::create([
            'correction_id' => $correction->id,
            'break_start' => '12:00',
            'break_end' => '13:00',
        ]);

        // 承認処理呼び出し
        $response = $this->actingAs($this->admin, 'admin')
            ->patch("/admin/stamp_correction_request/approve/{$correction->id}");

        $response->assertRedirect();

        // 修正申請が承認済みになったか
        $this->assertDatabaseHas('attendance_corrections', [
            'id' => $correction->id,
            'applications_status_id' => 2,
        ]);

        // Attendance が修正されているか
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'work_in' => '10:00:00',
            'work_out' => '19:00:00',
        ]);

        // BreakTime にコピーされているか
        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);
    }

}
