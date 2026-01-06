<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Models\User;

class UserEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 会員登録後に認証メールが送信される()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);

        // メール送信
        $user->sendEmailVerificationNotification();

        Notification::assertSentTo(
            [$user], VerifyEmail::class
        );
    }

    /** @test */
    public function メール認証誘導画面から認証サイトに遷移できる()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->get('/email/verify'); // 誘導画面

        $response->assertStatus(200);
        $response->assertSee('認証はこちらから'); // ボタン表示確認
    }

    /** @test */
    public function メール認証完了後に勤怠登録画面に遷移する()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // メール認証URL生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)
            ->get($verificationUrl);

        $response->assertRedirect('/attendance');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}

