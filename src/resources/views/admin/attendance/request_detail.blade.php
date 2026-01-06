@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/request_detail.css') }}">
@endsection

@section('content')
    <div class="request-detail-page">
        <div class="title-box">
            <h2 class="page-title">勤怠詳細</h2>
        </div>

        <div class="detail-card">
            {{-- 名前 --}}
            <div class="detail-row">
                <div class="label-name">名前</div>
                <div class="value-box">
                    <div class="value-text">
                        {{ $correction->user->name }}
                    </div>
                </div>
            </div>

            {{-- 日付 --}}
            <div class="detail-row">
                <div class="label-name">日付</div>
                <div class="value-box">
                    <div class="value-text">
                        {{ $correction->attendance->work_date->format('Y年 n月 j日') }}
                    </div>
                </div>
            </div>

            @php
                $attendance = $correction->attendance;

                // 承認待ちの場合は修正申請の時間を優先
                $workIn  = $correction->applications_status_id == 1
                            ? $correction->request_start_time ?? $attendance->work_in
                            : $attendance->work_in;

                $workOut = $correction->applications_status_id == 1
                            ? $correction->request_end_time ?? $attendance->work_out
                            : $attendance->work_out;

                // 休憩も同様に切り替え
                $breaks = $correction->applications_status_id == 1 && $correction->breaks->isNotEmpty()
                            ? $correction->breaks
                            : $attendance->breaks;

                $breakCount = $breaks->count();
                $displayCount = max(2, $breakCount);
            @endphp

            {{-- 出勤・退勤 --}}
            <div class="detail-row">
                <div class="label-name">出勤・退勤</div>
                <div class="value-box">
                    <div class="value-text">
                        {{ substr($correction->request_start_time ?? $correction->attendance->work_in, 0, 5) }}
                        <span class="tilde">〜</span>
                        {{ substr($correction->request_end_time ?? $correction->attendance->work_out, 0, 5) }}
                    </div>
                </div>
            </div>


            {{-- 休憩 --}}
            @for($i = 0; $i < $displayCount; $i++)
                @php
                    $break = $breaks[$i] ?? null;
                    $breakStart = $break->break_start ?? '';
                    $breakEnd   = $break->break_end ?? '';
                @endphp
                <div class="detail-row">
                    <label class="label-name">休憩{{ $i + 1 }}</label>
                    <div class="value-box">
                        <input type="time" name="breaks[{{ $i }}][start]" value="{{ $breakStart }}">
                        ～
                        <input type="time" name="breaks[{{ $i }}][end]" value="{{ $breakEnd }}">
                    </div>
                </div>
            @endfor

            {{-- 備考（理由） --}}
            <div class="detail-row">
                <div class="label-name">備考</div>
                <div class="value-box">
                    <div class="note">
                        {{ $correction->reason }}
                    </div>
                </div>
            </div>

        </div>

        {{-- 承認ボタン --}}
        <div class="button-area">
            @if($correction->applications_status_id == 1)
            <form action="{{ route('admin.attendance_correction.approve', $correction->id) }}" method="POST">
                @csrf
                <button class="approve-btn">承認</button>
            </form>
            @else
                <div class="approved-label">承認済み</div>
            @endif
        </div>
    </div>
@endsection
