@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
@endsection

@section('content')
    <div class="attendance-list">

        {{-- タイトル --}}
        <div class="title-box">
            <h1 class="page-title">{{ $user->name }} さんの勤怠</h1>
        </div>
        {{-- 月切替 --}}
        <div class="month-selector">
            <a href="{{ route('admin.attendance.staff', [
                    'id' => $user->id,
                    'month' => $month->copy()->subMonth()->format('Y-m')
                ]) }}" class="prev-month">←前月</a>

            <form method="GET"
                action="{{ route('admin.attendance.staff', ['id' => $user->id]) }}"
                class="month-form">

                <input
                    type="month"
                    name="month"
                    value="{{ $month->format('Y-m') }}"
                    onchange="this.form.submit()"
                    class="month-input"
                >

            </form>

            <a href="{{ route('admin.attendance.staff', [
                    'id' => $user->id,
                    'month' => $month->copy()->addMonth()->format('Y-m')
                ]) }}" class="next-month">翌月→</a>
        </div>


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
            @foreach ($dates as $date)
                @php
                    $attendance = $attendances[$date->format('Y-m-d')] ?? null;

                    $workIn   = $attendance?->work_in ? substr($attendance->work_in, 0, 5) : '';
                    $workOut  = $attendance?->work_out ? substr($attendance->work_out, 0, 5) : '';
                    $break    = $attendance?->break_total ? substr($attendance->break_total, 0, 5) : '';
                    $workTime = $attendance?->work_total ? substr($attendance->work_total, 0, 5) : '';

                    // 詳細リンク（勤怠なしの日も日付で遷移可能）
                    $detailParam = $attendance ? $attendance->id : $date->format('Y-m-d');
                @endphp

                <tr>
                    <td>{{ $date->isoFormat('MM/DD(ddd)') }}</td>
                    <td>{{ $workIn }}</td>
                    <td>{{ $workOut }}</td>
                    <td>{{ $break }}</td>
                    <td>{{ $workTime }}</td>
                    <td>
                    @if($attendance)
                            <a href="{{ route('admin.attendance.detail', $attendance->id) }}"
                                class="detail-link">
                                詳細
                            </a>
                        @else
                            <a href="{{ route('admin.attendance.detail', $date->format('Y-m-d')) }}
                                    ?user_id={{ $user->id }}"
                                class="detail-link">
                                詳細
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>

        </table>

        {{-- CSV出力 --}}
        <form action="{{ route('admin.attendance.staff.csv', ['id' => $user->id]) }}" method="GET">
            <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
            <button type="submit" class="csv-btn">CSV出力</button>
        </form>

    </div>
@endsection
