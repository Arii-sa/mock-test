<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use App\Models\User;
use App\Http\Requests\LoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
                return view('auth.register');
            });

        Fortify::loginView(function () {
                return view('auth.login');
            });

        Fortify::authenticateUsing(function (Request $request) {
            if ($request->is('admin/*')) {
                return null;
            }

            app(LoginRequest::class)->validateResolved();

            $user = User::where('email', $request->email)->first();

            if ($user && !$user->hasVerifiedEmail()) {
                session()->flash('login_error', 'メール認証が完了していません。');
                return null;
            }

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }

            session()->flash('login_error', 'ログイン情報が登録されていません');
            return null;
        });

        RateLimiter::for('login', function (Request $request) {
                $email = (string) $request->email;

                return Limit::perMinute(10)->by($email . $request->ip());
            });
    }
}
