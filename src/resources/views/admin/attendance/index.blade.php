@extends('layouts.admin')

@section('title', '日次勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/index.css') }}">
@endsection

@section('content')
<div class="attendance-container">

    {{-- タイトル --}}
    <h2 class="attendance-title">{{ $date->format('Y年m月d日の勤怠') }}</h2>

    {{-- 日付切替 --}}
    <div class="date-switch">
        <a href="{{ route('admin.attendance.list', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}" class="prev-day">
            <span>＜ 前日</span>
        </a>

        <div class="current-date">
            <input type="text" value="{{ $date->format('Y/m/d') }}" readonly>
        </div>

        <a href="{{ route('admin.attendance.list', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}" class="next-day">
            <span>翌日 ＞</span>
        </a>
    </div>

    {{-- 勤怠テーブル --}}
    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->user->name }}</td>

                    {{-- 出勤時間 --}}
                    <td>
                        {{ $attendance->work_in ? substr($attendance->work_in, 0, 5) : '' }}
                    </td>

                    {{-- 退勤時間 --}}
                    <td>
                        {{ $attendance->work_out ? substr($attendance->work_out, 0, 5) : '' }}
                    </td>

                    {{-- 休憩時間（合計） --}}
                    <td>
                        {{ $attendance->break_total ? substr($attendance->break_total, 0, 5) : '' }}
                    </td>

                    {{-- 合計時間 --}}
                    <td>
                    {{ $attendance->work_total ? substr($attendance->work_total, 0, 5) : '' }}
                    </td>

                    {{-- 詳細 --}}
                    <td>
                        <a class="detail-link"
                           href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}">
                           詳細
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="no-data">データがありません</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection
