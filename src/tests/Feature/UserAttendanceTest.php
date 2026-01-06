<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Carbon\Carbon;

class UserAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト用ユーザーを作成
        $this->user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
    }

    /** @test */
    public function 勤怠画面に現在日時が正しく表示されている()
    {
        // ユーザーでログイン
        $this->actingAs($this->user);

        // 勤怠打刻画面にアクセス
        $response = $this->get('/attendance'); // 実際のルートに合わせて修正

        $response->assertStatus(200);

        $nowDate = Carbon::now()->isoFormat('YYYY年M月D日(ddd)');
        $response->assertSee($nowDate);
        // 時間
        $nowTime = Carbon::now()->format('H:i');
        $response->assertSee($nowTime);
    }
}

