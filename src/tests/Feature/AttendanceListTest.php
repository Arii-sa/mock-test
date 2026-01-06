<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Status;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\StatusesTableSeeder::class);
    }

    /** @test */
    public function 自分の勤怠情報がすべて勤怠一覧に表示される()
    {
        $user = User::factory()->create();

        // 今月の勤怠を2日分作成
        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today()->subDays(1),
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => Status::where('name', '退勤済')->value('id'),
        ]);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'work_in'   => '10:00:00',
            'work_out'  => '19:00:00',
            'status_id' => Status::where('name', '退勤済')->value('id'),
        ]);

        $this->actingAs($user)
            ->get('/attendance/list')
            ->assertStatus(200)
            ->assertSee('09:00')
            ->assertSee('18:00')
            ->assertSee('10:00')
            ->assertSee('19:00');
    }

    /** @test */
    public function 勤怠一覧画面を開いた際に現在の月が表示される()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/attendance/list')
            ->assertStatus(200)
            ->assertSee('value="' . now()->format('Y-m') . '"', false);
    }

    /** @test */
    public function 前月ボタン押下で前月の勤怠情報が表示される()
    {
        $user = User::factory()->create();

        $lastMonth = now()->subMonth();

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => $lastMonth->copy()->startOfMonth()->addDay(),
            'work_in'   => '08:30:00',
            'work_out'  => '17:30:00',
            'status_id' => Status::where('name', '退勤済')->value('id'),
        ]);

        $this->actingAs($user)
            ->get('/attendance/list?month=' . $lastMonth->format('Y-m'))
            ->assertStatus(200)
            ->assertSee('value="' . $lastMonth->format('Y-m') . '"', false)
            ->assertSee('08:30')
            ->assertSee('17:30');
    }

    /** @test */
    public function 翌月ボタン押下で翌月の勤怠情報が表示される()
    {
        $user = User::factory()->create();

        $nextMonth = now()->addMonth();

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => $nextMonth->copy()->startOfMonth()->addDay(),
            'work_in'   => '11:00:00',
            'work_out'  => '20:00:00',
            'status_id' => Status::where('name', '退勤済')->value('id'),
        ]);

        $this->actingAs($user)
            ->get('/attendance/list?month=' . $nextMonth->format('Y-m'))
            ->assertStatus(200)
            ->assertSee('value="' . $nextMonth->format('Y-m') . '"', false)
            ->assertSee('11:00')
            ->assertSee('20:00');
    }

    /** @test */
    public function 詳細ボタン押下で勤怠詳細画面に遷移できる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'work_in'   => '09:00:00',
            'work_out'  => '18:00:00',
            'status_id' => Status::where('name', '退勤済')->value('id'),
        ]);

        $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->work_date->format('Y-m-d'))
            ->assertStatus(200)
            ->assertSee('09:00')
            ->assertSee('18:00');
    }
}

