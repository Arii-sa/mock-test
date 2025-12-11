@extends('layouts.user')

@section('title', '管理者ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/auth/login.css') }}">
@endsection

@section('content')
<div class="login-container">

    <h2 class="login-title">管理者ログイン</h2>

    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf

        {{-- メールアドレス --}}
        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}">
            @error('email')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        {{-- パスワード --}}
        <div class="form-group">
            <label for="password">パスワード</label>
            <input id="password" type="password" name="password">
            @error('password')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        {{-- 認証全体のエラー（ログイン情報が正しくない等） --}}
        @if($errors->has('auth'))
            <span class="error">{{ $errors->first('auth') }}</span>
        @endif

        {{-- ボタン --}}
        <button type="submit" class="btn-login">ログイン</button>

    </form>

</div>
@endsection

