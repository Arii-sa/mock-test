@extends('layouts.user')

@section('content')
    <div class="verify-container">
        <h1>メール認証が必要です</h1>
        <p>登録したメールアドレス宛に認証メールを送信しました。</p>
        <p>メール内のリンクをクリックして認証を完了してください。</p>

        @if (session('message'))
            <p class="success">{{ session('message') }}</p>
        @endif

        <div style="margin: 20px 0;">
            <a href="{{ route('verification.notice') }}" class="verify-link">
                認証はこちらから
            </a>
        </div>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit">認証メールを再送する</button>
        </form>
    </div>
@endsection
