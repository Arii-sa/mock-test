@extends('layouts.user')

@section('title', 'ログイン')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
    <div class="login-container">

        <form class="form" method="POST" action="{{ route('login.submit') }}">
            @csrf

            <h1 class="login-title">ログイン</h1>

            <div class="form-group">
                <div class="form-group__label">
                    <label for="email" class="label-name">メールアドレス</label>
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

            <div class="login-btn">
                <button type="submit" class="btn-name">ログインする</button>
            </div>
            <div class="link-btn">
                <p class="register-link">
                    <a href="{{ route('register') }}">会員登録はこちら</a>
                </p>
            </div>
        </form>
    </div>
@endsection
