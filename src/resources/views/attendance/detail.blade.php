@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')
    <div class="detail-container">

        {{-- 承認待ち → 編集不可メッセージ --}}
        @if($attendance->status_id == 5)
            <div class="alert alert-warning">
                承認待ちのため修正はできません。
            </div>
        @endif

        <div class="title-box">
            <h2 class="page-title">勤怠詳細</h2>
        </div>

        @php
            // 最新の修正申請を取得
            $latestCorrection = $latestCorrection ?? $attendance->corrections()->latest()->first();

            // 休憩データ
            if ($latestCorrection && $latestCorrection->breaks->isNotEmpty()) 
                {
                    $breaksToShow = $latestCorrection->breaks;
                }
                // Attendance がDBに存在する場合のみ breaks を取得
                elseif ($attendance->id ?? false) {
                    $breaksToShow = $attendance->breaks ?? collect();
                }
                // Attendance がない場合は空コレクション
                else {
                    $breaksToShow = collect();
                }
            $breakCount = $breaksToShow->count();
        @endphp

        <form action="{{ route('attendance.request', $attendance->id ?? $attendance->work_date->format('Y-m-d')) }}" method="POST">
        @csrf
            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

            <div class="attendance-detail__table">
                {{-- 名前 --}}
                <div class="detail-row">
                    <label class="label-name">名前</label>
                    <div class="value-box">
                        <div class="value-text">
                            {{ Auth::user()->name }}
                        </div>
                    </div>
                </div>

                {{-- 日付 --}}
                <div class="detail-row">
                    <label class="label-name">日付</label>
                    <div class="value-box">
                        <div class="value-text">
                            {{ $attendance->work_date->format('Y年n月j日') }}
                        </div>
                    </div>
                </div>

                {{-- 出勤・退勤 --}}
                @php
                    $workIn  = old('work_in', $latestCorrection->request_start_time ?? $attendance->work_in);
                    $workOut = old('work_out', $latestCorrection->request_end_time ?? $attendance->work_out);
                @endphp
                <div class="detail-row">
                    <label class="label-name">出勤・退勤</label>
                    <div class="value-box">
                        <input type="time" name="work_in"
                            value="{{ $workIn ? substr($workIn, 0, 5) : '' }}"
                            {{ $attendance->status_id == 5 ? 'readonly' : '' }}>
                        ～
                        <input type="time" name="work_out"
                            value="{{ $workOut ? substr($workOut, 0, 5) : '' }}"
                            {{ $attendance->status_id == 5 ? 'readonly' : '' }}>
                    </div>
                    <div class="detail-row__error">
                        @error('work_in')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                        @error('work_out')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- 休憩（既存 + 修正申請） --}}
                @foreach($breaksToShow as $i => $break)
                    @php
                        $breakStart = old("breaks.$i.start", $break->break_start ? substr($break->break_start, 0, 5) : '');
                        $breakEnd   = old("breaks.$i.end", $break->break_end   ? substr($break->break_end, 0, 5) : '');
                    @endphp
                    <div class="detail-row">
                        <label class="label-name">休憩{{ $i + 1 }}</label>
                        <div class="value-box">
                            <input type="time" name="breaks[{{ $i }}][start]" value="{{ $breakStart }}" {{ $attendance->status_id == 5 ? 'readonly' : '' }}>
                            ～
                            <input type="time" name="breaks[{{ $i }}][end]" value="{{ $breakEnd }}" {{ $attendance->status_id == 5 ? 'readonly' : '' }}>
                        </div>
                    </div>
                    <div class="detail-row__error">
                        @error("breaks.$i.start")
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                        @error("breaks.$i.end")
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach

                {{-- 追加休憩入力欄 --}}
                <div class="detail-row">
                    <label class="label-name">休憩{{ $breakCount + 1 }}</label>
                    <div class="value-box">
                        <input type="time" name="breaks_new[start]" {{ $attendance->status_id == 5 ? 'readonly' : '' }}>
                        ～
                        <input type="time" name="breaks_new[end]" {{ $attendance->status_id == 5 ? 'readonly' : '' }}>
                    </div>
                    <div class="detail-row__error">
                        @error('breaks_new.start')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                        @error('breaks_new.end')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- 備考 --}}
                <div class="detail-row">
                    <label class="label-name">備考</label>
                    <div class="value-box">
                        <textarea class="note" name="reason" {{ $attendance->status_id == 5 ? 'readonly' : '' }}>{{ old('reason', $latestCorrection->reason ?? $attendance->note) }}</textarea>
                    </div>
                    <div class="detail-row__error">
                        @error('reason')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ボタン --}}
            <div class="button-area">
                @if(empty($latestCorrection))
                    <button type="submit" class="btn-submit">修正</button>
                @elseif($latestCorrection->applications_status_id === 1)
                    <p class="btn-message">承認待ちのため修正はできません。</p>
                @elseif($latestCorrection->applications_status_id === 2)
                    <p class="edited-btn-message">修正済み</p>
                @endif
            </div>

        </form>
    </div>
@endsection