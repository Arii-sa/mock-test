@extends('layouts.app')

@section('title', '勤怠登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <div class="status-label">
        <span>{{ $statusLabel ?? '勤務外' }}</span>
    </div>

    <div class="date">{{ now()->format('Y年n月j日(D)') }}</div>
    <div class="time" id="current-time">{{ now()->format('H:i') }}</div>

    <div class="button-group">
        {{-- ステータスによってボタン切り替え --}}
        @if($status === 'off')
            <form method="POST" action="{{ route('attendance.start') }}">
                @csrf
                <button type="submit" class="btn btn-start">出勤</button>
            </form>
        @elseif($status === 'working')
            <form method="POST" action="{{ route('attendance.breakIn') }}">
                @csrf
                <button type="submit" class="btn btn-break">休憩入</button>
            </form>
            <form method="POST" action="{{ route('attendance.leave') }}">
                @csrf
                <button type="submit" class="btn btn-leave">退勤</button>
            </form>
        @elseif($status === 'break')
            <form method="POST" action="{{ route('attendance.breakOut') }}">
                @csrf
                <button type="submit" class="btn btn-break-return">休憩戻</button>
            </form>
        @elseif($status === 'finished')
            <div class="message">お疲れ様でした。</div>
        @endif
    </div>
</div>
@endsection
