<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * 会員登録画面表示
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * 会員登録処理
     */
    public function register(RegisterRequest $request)
    {
        // 新規ユーザー作成
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        // 登録後ログインさせる
        Auth::login($user);

        // メール認証を有効にしているなら、まず認証ページへ
        if (is_null($user->email_verified_at)) {
            return redirect()->route('verification.notice');
        }

        return redirect()->route('attendance.index');
    }


    /**
     * ログイン画面表示
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        // バリデーション済みデータ取得
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // メール未認証チェック
            if (is_null($user->email_verified_at)) {
                Auth::logout();
                return redirect()->route('verification.notice');
            }

            // セッション再生成
            $request->session()->regenerate();

            // ログイン成功
            return redirect()->intended(route('attendance.index'));
        }

        // 認証失敗
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->withInput($request->only('email'));
    }
    
    /**
     * ログアウト処理
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // メール認証待機画面（未認証ログイン時）
    public function showVerifyNotice()
    {
        return view('auth.verify-notice');
    }

}
