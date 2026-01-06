@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/list.css') }}">
@endsection

@section('content')
    <div class="attendance-list">
        <div class="title-box">
            <h1 class="page-title">勤怠一覧</h1>
        </div>

        <div class="month-selector">
            {{-- 前月 --}}
            <a href="{{ route('attendance.list', [
                    'month' => \Carbon\Carbon::parse($month)->subMonth()->format('Y-m')
                ]) }}" class="prev-month">
                ←前月
            </a>

            {{-- 月選択（カレンダー） --}}
            <form method="GET"
                action="{{ route('attendance.list') }}"
                class="month-form">

                <input
                    type="month"
                    name="month"
                    value="{{ \Carbon\Carbon::parse($month)->format('Y-m') }}"
                    onchange="this.form.submit()"
                    class="month-input"
                >
            </form>

            {{-- 翌月 --}}
            <a href="{{ route('attendance.list', [
                    'month' => \Carbon\Carbon::parse($month)->addMonth()->format('Y-m')
                ]) }}" class="next-month">
                翌月→
            </a>
        </div>


        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>勤務時間</th>
                    <th>詳細</th>
                </tr>
            </thead>

            <tbody>
                @foreach($dates as $date)
                    @php
                        $attendance = $attendances[$date->format('Y-m-d')] ?? null;
                        $correction = $corrections[$date->format('Y-m-d')] ?? null;

                        // ★ 申請中を最優先で表示
                        if ($correction && $correction->applications_status_id === 1) {
                            $workIn  = $correction->request_start_time ? substr($correction->request_start_time, 0, 5) : '';
                            $workOut = $correction->request_end_time   ? substr($correction->request_end_time, 0, 5) : '';
                            $break   = '';      // 必要なら計算
                            $workTime = '';     // 承認前は未確定でもOK
                        }
                        // 通常の勤怠
                        elseif ($attendance) {
                            $workIn   = $attendance->work_in   ? substr($attendance->work_in, 0, 5) : '';
                            $workOut  = $attendance->work_out  ? substr($attendance->work_out, 0, 5) : '';
                            $break    = $attendance->break_total ? substr($attendance->break_total, 0, 5) : '';
                            $workTime = $attendance->work_total ? substr($attendance->work_total, 0, 5) : '';
                        }
                        else {
                            $workIn = $workOut = $break = $workTime = '';
                        }

                        $detailParam = $attendance?->id ?? $date->format('Y-m-d');
                    @endphp



                    <tr>
                        <td>{{ $date->isoFormat('MM/DD(ddd)') }}</td>

                        <td>{{ $workIn }}</td>
                        <td>{{ $workOut }}</td>

                        <td>{{ $break }}</td>
                        <td>{{ $workTime }}</td>

                        <td>
                            <a href="{{ route('attendance.detail', $detailParam) }}" class="detail-link">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
