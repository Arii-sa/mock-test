{{-- 共通レイアウト：ヘッダー付き --}}
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '勤怠管理')</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/app.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header-left">
            <h1 class="logo">COACHTECH</h1>
        </div>
        
        <nav class="header-right">
        @if (Auth::check())
            <a href="{{ route('attendance.index') }}">勤怠</a>
            <a href="{{ route('attendance.list') }}">勤怠一覧</a>
            <a href="{{ route('attendance_correction.list') }}">申請</a>
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
