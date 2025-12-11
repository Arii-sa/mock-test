@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/request_detail.css') }}">
@endsection

@section('content')
<div class="request-detail-page">

    <h2 class="page-title">勤怠詳細</h2>

    <div class="detail-card">

        {{-- 名前 --}}
        <div class="row">
            <div class="label">名前</div>
            <div class="value">{{ $correction->user->name }}</div>
        </div>

        {{-- 日付 --}}
        <div class="row">
            <div class="label">日付</div>
            <div class="value">
                {{ $correction->attendance->work_date->format('Y年 n月 j日') }}
            </div>
        </div>

        {{-- 出勤・退勤 --}}
        <div class="row">
            <div class="label">出勤・退勤</div>
            <div class="value">
                {{ substr($correction->request_start_time, 0, 5) }}
                <span class="tilde">〜</span>
                {{ substr($correction->request_end_time, 0, 5) }}
            </div>
        </div>

        {{-- 休憩 --}}
        @if($correction->breaks->isEmpty())
            <div class="row">
                <div class="label">休憩</div>
                <div class="value">なし</div>
            </div>
        @else
            @foreach($correction->breaks as $index => $break)
                <div class="row">
                    <div class="label">休憩{{ $index+1 }}</div>
                    <div class="value">
                        {{ substr($break->break_start, 0, 5) }}
                        <span class="tilde">〜</span>
                        {{ substr($break->break_end, 0, 5) }}
                    </div>
                </div>
            @endforeach
        @endif

        {{-- 備考（理由） --}}
        <div class="row">
            <div class="label">備考</div>
            <div class="value">{{ $correction->reason }}</div>
        </div>

    </div>

    {{-- 承認ボタン --}}
    @if($correction->applications_status_id == 1)
        <form action="{{ route('admin.attendance_correction.approve', $correction->id) }}" method="POST">
            @csrf
            <button class="approve-btn">承認</button>
        </form>
    @endif

</div>
@endsection
