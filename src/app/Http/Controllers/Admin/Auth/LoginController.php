<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AdminLoginRequest;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // 追加：ログイン試行回数レートリミットなど、必要なら入れる
        if (Auth::guard('admin')->attempt($credentials, $request->filled('remember'))) {
            // 認証成功
            $request->session()->regenerate();
            return redirect()->intended(route('admin.attendance.list'));
        }

        // 認証失敗
        return back()->withErrors([
            'auth' => 'ログイン情報が登録されていません',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}

