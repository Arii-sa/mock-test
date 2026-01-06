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

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 必要Seeder
        $this->seed(\Database\Seeders\StatusesTableSeeder::class);
        $this->seed(\Database\Seeders\AdminSeeder::class);
    }

    /** @test */
    public function 管理者はスタッフ一覧で全ユーザーの氏名とメールを確認できる()
    {
        $admin = Admin::first();

        $users = User::factory()->count(3)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.staff.list'));

        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    /** @test */
    public function 管理者は選択したユーザーの勤怠一覧を確認できる()
    {
        $admin = Admin::first();
        $user  = User::factory()->create();

        $status = Status::first();

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $status->id,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.staff', $user->id));

        $response->assertStatus(200);
        $response->assertSee('01/10');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 前月ボタンで前月の勤怠が表示される()
    {
        $admin = Admin::first();
        $user  = User::factory()->create();
        $status = Status::first();

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2025-12-10',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $status->id,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.staff', [
                'id' => $user->id,
                'month' => '2025-12'
            ]));

        $response->assertStatus(200);
        $response->assertSee('12/10');
    }

    /** @test */
    public function 翌月ボタンで翌月の勤怠が表示される()
    {
        $admin = Admin::first();
        $user  = User::factory()->create();
        $status = Status::first();

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-02-10',
            'work_in'   => '10:00:00',
            'work_out'  => '19:00:00',
            'status_id' => $status->id,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.staff', [
                'id' => $user->id,
                'month' => '2026-02'
            ]));

        $response->assertStatus(200);
        $response->assertSee('02/10');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function 詳細ボタンで勤怠詳細画面へ遷移できる()
    {
        $admin = Admin::first();
        $user  = User::factory()->create();
        $status = Status::first();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-15',
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => $status->id,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.detail', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}

