@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
@endsection

@section('content')
<div class="attendance-list">

    {{-- タイトル --}}
    <h1>{{ $user->name }} さんの勤怠一覧</h1>

    {{-- 月切替 --}}
    <div class="month-selector">
        <a href="{{ route('admin.attendance.staff', [
                'id' => $user->id,
                'month' => $currentMonth->copy()->subMonth()->format('Y-m')
            ]) }}" class="prev-month">前月</a>

        <span class="current-month">{{ $currentMonth->format('Y年m月') }}</span>

        <a href="{{ route('admin.attendance.staff', [
                'id' => $user->id,
                'month' => $currentMonth->copy()->addMonth()->format('Y-m')
            ]) }}" class="next-month">翌月</a>
    </div>

    {{-- CSV出力 --}}
    <form action="{{ route('admin.attendance.staff.csv', ['id' => $user->id]) }}" method="GET">
        <input type="hidden" name="month" value="{{ $currentMonth->format('Y-m') }}">
        <button type="submit" class="csv-btn">CSV出力</button>
    </form>

    {{-- 勤怠テーブル --}}
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
            @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('m/d(D)') }}</td>

                    <td>{{ $attendance->work_in ? substr($attendance->work_in, 0, 5) : '' }}</td>
                    <td>{{ $attendance->work_out ? substr($attendance->work_out, 0, 5) : '' }}</td>

                    <td>{{ $attendance->break_total ? substr($attendance->break_total, 0, 5) : '' }}</td>
                    <td>{{ $attendance->work_total ? substr($attendance->work_total, 0, 5) : '' }}</td>

                    <td>
                        <a href="{{ route('admin.attendance.detail', $attendance->id) }}" class="detail-link">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
