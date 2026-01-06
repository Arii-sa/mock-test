@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/detail.css') }}">
@endsection

@section('content')
    @php
        $latestCorrection = $attendance->corrections()->latest()->first();
        $isPending = $latestCorrection && $latestCorrection->applications_status_id === 1;

        // ---------------------------
        // 表示用データ決定
        // ---------------------------
        if($isPending) {
            // 申請中 → 修正申請データを表示
            $displayWorkIn  = old('work_in', $latestCorrection->request_start_time ?? '');
            $displayWorkOut = old('work_out', $latestCorrection->request_end_time ?? '');
            $displayBreaks  = $latestCorrection->breaks ?? collect();
            $displayReason  = old('reason', $latestCorrection->reason ?? '');
        } else {
            // 通常 or 承認済み → 勤怠データを表示
            $displayWorkIn  = old('work_in', $attendance->work_in ? substr($attendance->work_in,0,5) : '');
            $displayWorkOut = old('work_out', $attendance->work_out ? substr($attendance->work_out,0,5) : '');
            $displayBreaks  = $attendance->breaks ?? collect();
            $displayReason  = old('reason', $attendance->note ?? '');
        }

        $breakCount = $displayBreaks->count();
        $displayCount = max(2, $breakCount); // 最低2枠
    @endphp

    <div class="attendance-detail-container">
        <div class="title-box">
            <h2 class="page-title">勤怠詳細</h2>
        </div>

        <form action="{{ route('admin.attendance.update', $attendance->id ?? $attendance->work_date) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="attendance-detail__table">
                <input type="hidden" name="user_id" value="{{ $attendance->user->id }}">

                {{-- 名前 --}}
                <div class="detail-row">
                    <label class="label-name">名前</label>
                    <div class="value-box">
                        <div class="value-text">{{ $attendance->user->name }}</div>
                    </div>
                </div>

                {{-- 日付 --}}
                <div class="detail-row">
                    <label class="label-name">日付</label>
                    <div class="value-box">
                        <div class="value-text">{{ $attendance->work_date->format('Y年n月j日') }}</div>
                    </div>
                </div>

                {{-- 出勤・退勤 --}}
                <div class="detail-row">
                    <label class="label-name">出勤・退勤</label>
                    <div class="value-box">
                        <input type="time" name="work_in" value="{{ substr($displayWorkIn,0,5) }}">
                        ～
                        <input type="time" name="work_out" value="{{ substr($displayWorkOut,0,5) }}">
                    </div>
                    <div class="detail-row__error">
                        @error('work_in') <p class="error-message">{{ $message }}</p> @enderror
                        @error('work_out') <p class="error-message">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- 休憩 --}}
                @for($i = 0; $i < $displayCount; $i++)
                    @php
                        $break = $displayBreaks[$i] ?? null;
                        $breakStart = $break && $break->break_start ? substr($break->break_start,0,5) : '';
                        $breakEnd   = $break && $break->break_end   ? substr($break->break_end,0,5) : '';
                    @endphp
                    <div class="detail-row">
                        <label class="label-name">休憩{{ $i + 1 }}</label>
                        <div class="value-box">
                            <input type="time" name="breaks[{{ $i }}][start]" value="{{ $breakStart }}">
                            ～
                            <input type="time" name="breaks[{{ $i }}][end]" value="{{ $breakEnd }}">
                        </div>
                        <div class="detail-row__error">
                            @error("breaks.$i.start") <p class="error-message">{{ $message }}</p> @enderror
                            @error("breaks.$i.end") <p class="error-message">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endfor

                {{-- 備考 --}}
                <div class="detail-row">
                    <label class="label-name">備考</label>
                    <div class="value-box">
                        <textarea name="reason" class="note">{{ old('reason', $attendance->note ?? '') }}</textarea>
                    </div>
                    <div class="detail-row__error">
                        @error('reason') <p class="error-message">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- メッセージ --}}
            @if(session('message'))
                <p class="session-message">{{ session('message') }}</p>
            @endif

            {{-- ボタン --}}
            <div class="button-area">
                @if(!$isPending)
                    <button type="submit" class="btn-submit">修正</button>
                @else
                    <p class="btn-message">承認待ちのため修正はできません。</p>
                @endif
            </div>

        </form>
    </div>
@endsection
