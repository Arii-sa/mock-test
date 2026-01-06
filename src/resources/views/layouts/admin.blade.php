<!DOCTYPE html>

<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', '勤怠管理')</title>
        <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
        <link rel="stylesheet" href="{{ asset('css/layouts/admin.css') }}">
        @yield('css')
    </head>
    <body>
        <header class="header">
            <div class="header__logo">
                <img src="{{ asset('image/logo.png') }}" alt="ロゴ">
            </div>

            <nav class="header-right">
            @if (Auth::guard('admin')->check())
                <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
                <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
                <a href="{{ route('admin.attendance_correction.request') }}">申請一覧</a>
                {{-- Fortify使用：ログアウト --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">ログアウト</button>
                </form>
            @endif
            </nav>

        </header>

        <main>
            @yield('content')
        </main>
    </body>
</html>
