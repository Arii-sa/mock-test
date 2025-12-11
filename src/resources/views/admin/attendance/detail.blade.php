@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">

    <h2 class="page-title">勤怠詳細</h2>

    <form action="{{ route('attendance.request', $attendance->id) }}" method="POST">
    @csrf

        {{-- 名前 --}}
        <div class="detail-row">
            <label>名前</label>
            <div class="value-box">{{ $attendance->user->name }}</div>
        </div>

        {{-- 日付 --}}
        <div class="detail-row">
            <label>日付</label>
            <div class="value-box">
                {{ $attendance->work_date->format('Y年n月j日') }}
            </div>
        </div>

        {{-- 出勤・退勤 --}}
        @php
            $workIn  = $attendance->work_in  ? substr($attendance->work_in, 0, 5) : '';
            $workOut = $attendance->work_out ? substr($attendance->work_out, 0, 5) : '';
        @endphp

        <div class="detail-row">
            <label>出勤・退勤</label>
            <div class="time-edit">
                <input type="time" name="work_in"
                    value="{{ $workIn }}"
                    {{ $attendance->status_id == 5 ? 'readonly' : '' }}>

                ～

                <input type="time" name="work_out"
                    value="{{ $workOut }}"
                    {{ $attendance->status_id == 5 ? 'readonly' : '' }}>
            </div>

            @error('work_in')
                <p class="error">{{ $message }}</p>
            @enderror
            @error('work_out')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        {{-- 休憩（既存分） --}}
        @foreach($attendance->breaks as $i => $break)
            @php
                $breakStart = $break->break_start ? substr($break->break_start, 0, 5) : '';
                $breakEnd   = $break->break_end   ? substr($break->break_end, 0, 5) : '';
            @endphp
            <div class="detail-row">
                <label>休憩{{ $i + 1 }}</label>
                <div class="time-edit">
                    <input type="time" name="breaks[{{ $i }}][start]"
                        value="{{ $breakStart }}"
                        {{ $attendance->status_id == 5 ? 'readonly' : '' }}>

                    ～

                    <input type="time" name="breaks[{{ $i }}][end]"
                        value="{{ $breakEnd }}"
                        {{ $attendance->status_id == 5 ? 'readonly' : '' }}>
                </div>
            </div>
        @endforeach

        {{-- 新しい休憩入力欄 --}}
        <div class="detail-row">
            <label>休憩{{ count($attendance->breaks) + 1 }}</label>
            <div class="time-edit">
                <input type="time" name="breaks_new[start]"
                    {{ $attendance->status_id == 5 ? 'readonly' : '' }}>
                ～
                <input type="time" name="breaks_new[end]"
                    {{ $attendance->status_id == 5 ? 'readonly' : '' }}>
            </div>
        </div>

        {{-- 備考 --}}
        <div class="detail-row">
            <label>備考</label>
            <textarea name="reason" {{ $attendance->status_id == 5 ? 'readonly' : '' }}>
                {{ old('reason', $latestCorrection->reason ?? $attendance->note) }}
            </textarea>

            @error('reason')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        @php
            // 修正不可 = 承認待ち
            $isPending = ($latestCorrection?->applications_status_id === 1);
        @endphp

        @if($isPending)
            <p class="text-red-500 text-center mt-2">
                承認待ちのため修正はできません。
            </p>
        @else
            <div class="button-area">
                <button type="submit" class="btn-submit">修正</button>
            </div>
        @endif


    </form>
</div>
@endsection
