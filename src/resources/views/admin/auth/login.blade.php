@extends('layouts.user')

@section('title', '管理者ログイン')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/auth/login.css') }}">
@endsection

@section('content')
    <div class="login-container">

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <div class="form-group">
                <h2 class="login-title">管理者ログイン</h2>
            </div>

            {{-- メールアドレス --}}
            <div class="form-group">
                <div class="form-group__label">
                    <label class="label-name" for="email">メールアドレス</label>
                </div>
                <div class="form-group__input">
                    <input class="input-space" id="email" type="email" name="email" value="{{ old('email') }}">
                </div>
                <div class="form-group__error">
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- パスワード --}}
            <div class="form-group">
                <div class="form-group__label">
                    <label for="password" class="label-name">パスワード</label>
                </div>
                <div class="form-group__input">
                    <input class="input-space" id="password" type="password" name="password">
                </div>
                <div class="form-group__error">
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- 認証全体のエラー（ログイン情報が正しくない等） --}}
            @if($errors->has('auth'))
                <span class="error-message">{{ $errors->first('auth') }}</span>
            @endif

            {{-- ボタン --}}
            <div class="btn-login">
                <button type="submit" class="btn-name">管理者ログインする</button>
            </div>
        </form>

    </div>
@endsection

