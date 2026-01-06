@extends('layouts.user')

@section('title', '会員登録')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
    <div class="register-container">

        <form class="form" method="POST" action="{{ route('register') }}">
            @csrf

            <h1 class="register-title">会員登録</h1>

            <div class="form-group">
                <div class="form-group__label">
                    <label class="label-name" for="name">名前</label>
                </div>
                <div class="form-group__input">
                    <input class="input-space" id="name" type="text" name="name" value="{{ old('name') }}">
                </div>
                <div class="form-group__error">
                    @error('name')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <div class="form-group__label">
                    <label class="label-name" for="email">メールアドレス</label>
                </div>
                <div class="form-group__input">
                    <input class="input-space" id="email" type="email" name="email" value="{{ old('email') }}">
                </div>
                <div class="form-group__error">
                    @error('email')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <div class="form-group__label">
                    <label class="label-name" for="password">パスワード</label>
                </div>
                <div class="form-group__input">
                    <input class="input-space" id="password" type="password" name="password">
                </div>
                <div class="form-group__error">
                    @error('password')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <div class="form-group__label">
                    <label class="label-name" for="password_confirmation">パスワード確認</label>
                </div>
                <div class="form-group__input">
                    <input class="input-space" id="password_confirmation" type="password" name="password_confirmation">
                </div>
                <div class="form-group__error">
                    @error('password_confirmation')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <button type="submit" class="btn-register">登録する</button>

            <p class="login-link">
                <a href="{{ route('login') }}">ログインはこちら</a>
            </p>
        </form>
    </div>
@endsection
