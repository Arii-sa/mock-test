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
                <span>← 前日</span>
            </a>

            <form method="GET" action="{{ route('admin.attendance.list') }}">
                <div class="current-date">
                    <input
                        type="date"
                        name="date"
                        value="{{ $date->format('Y-m-d') }}"
                        onchange="this.form.submit()"
                        class="date-input"
                    >
                </div>
            </form>

            <a href="{{ route('admin.attendance.list', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}" class="next-day">
                <span>翌日 →</span>
            </a>
        </div>

        {{-- 勤怠テーブル --}}
        <table class="attendance-table">
            <thead?>
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
                @foreach($rows as $row)
                <tr>
                    <td>{{ $row['user']->name }}</td>

                    <td>{{ $row['work_in'] ? substr($row['work_in'],0,5) : '' }}</td>
                    <td>{{ $row['work_out'] ? substr($row['work_out'],0,5) : '' }}</td>
                    <td>{{ $row['break'] ? substr($row['break'],0,5) : '' }}</td>
                    <td>{{ $row['total'] ? substr($row['total'],0,5) : '' }}</td>

                    <td>
                        @if($row['id'])
                            <a href="{{ route('admin.attendance.detail', $row['id']) }}">詳細</a>
                        @endif

                    </td>
                </tr>
                @endforeach

            </tbody>
        </table>

    </div>
@endsection
