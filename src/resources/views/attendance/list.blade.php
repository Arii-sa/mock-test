@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h1>勤怠一覧</h1>

    <div class="month-selector">
        <a href="{{ route('attendance.list', ['month' => \Carbon\Carbon::parse($month)->subMonth()->format('Y-m')]) }}" class="prev-month">前月</a>

        <span class="current-month">{{ \Carbon\Carbon::parse($month)->format('Y年m月') }}</span>

        <a href="{{ route('attendance.list', ['month' => \Carbon\Carbon::parse($month)->addMonth()->format('Y-m')]) }}" class="next-month">翌月</a>
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
                    // その日の勤怠データ（無ければ null）
                    $attendance = $attendances[$date->format('Y-m-d')] ?? null;
                    // 出勤無い日のリンク生成
                    $detailParam = $attendance ? $attendance->id : $date->format('Y-m-d');

                    $workIn   = $attendance?->work_in   ? substr($attendance->work_in, 0, 5) : '';
                    $workOut  = $attendance?->work_out  ? substr($attendance->work_out, 0, 5) : '';
                    $break    = $attendance?->break_total ? substr($attendance->break_total, 0, 5) : '';
                    $workTime = $attendance?->work_total  ? substr($attendance->work_total, 0, 5) : '';
                @endphp

                <tr>
                    <td>{{ $date->format('m/d(D)') }}</td>

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

    <button class="back-btn">戻る</button>
</div>
@endsection
