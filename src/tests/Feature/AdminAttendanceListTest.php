<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Status;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 外部キーで必須なSeeder
        $this->seed(\Database\Seeders\StatusesTableSeeder::class);
        $this->seed(\Database\Seeders\AdminSeeder::class);
    }

    /** @test */
    public function 管理者はその日の全ユーザーの勤怠情報を確認できる()
    {
        $admin = Admin::where('email', 'admin@example.com')->first();

        $users = User::factory()->count(2)->create();

        $status = Status::where('name', '退勤済')->first();

        foreach ($users as $user) {
            Attendance::create([
                'user_id'   => $user->id,
                'work_date' => today()->format('Y-m-d'),
                'work_in'   => '09:00:00',
                'work_out'  => '18:00:00',
                'status_id' => $status->id,
            ]);
        }

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee('09:00');
            $response->assertSee('18:00');
        }
        $response->assertSee(today()->format('Y-m-d'));
    }

    /** @test */
    public function 前日を押すと前日勤怠が表示される()
    {
        $admin = Admin::where('email', 'admin@example.com')->first();
        $user = User::factory()->create();
        $status = Status::where('name', '退勤済')->first();

        $yesterday = today()->subDay();
        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => $yesterday->format('Y-m-d'),
            'work_in'   => '08:30:00',
            'work_out'  => '17:30:00',
            'status_id' => $status->id,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list', ['date' => $yesterday->format('Y-m-d')]));

        $response->assertStatus(200);
        $response->assertSee($yesterday->format('Y-m-d'));
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }

    /** @test */
    public function 翌日を押すと翌日勤怠が表示される()
    {
        $admin = Admin::where('email', 'admin@example.com')->first();
        $user = User::factory()->create();
        $status = Status::where('name', '退勤済')->first();

        $tomorrow = today()->addDay();
        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => $tomorrow->format('Y-m-d'),
            'work_in'   => '10:00:00',
            'work_out'  => '19:00:00',
            'status_id' => $status->id,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list', ['date' => $tomorrow->format('Y-m-d')]));

        $response->assertStatus(200);
        $response->assertSee($tomorrow->format('Y-m-d'));
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }
}

