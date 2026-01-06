@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/request.css') }}">
@endsection

@section('content')
    <div class="request-container">
        <div class="request-box">
            <h2 class="page-title">申請一覧</h2>

            {{-- タブ切り替え --}}
            <div class="tabs">
                <a href="{{ route('attendance_correction.list', ['tab' => 'pending']) }}"
                    class="tab {{ $activeTab === 'pending' ? 'active' : '' }}">
                    承認待ち
                </a>
                <a href="{{ route('attendance_correction.list', ['tab' => 'approved']) }}"
                    class="tab {{ $activeTab === 'approved' ? 'active' : '' }}">
                    承認済み
                </a>
            </div>

            {{-- テーブル --}}
            <table class="table-area">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($requests as $req)
                        <tr>
                            <td>{{ $req->applicationStatus->name ?? '不明'}}</td>
                            <td>{{ $req->user->name }}</td>
                            <td>{{ $req->attendance->work_date->format('Y/m/d') }}</td>
                            <td>{{ $req->reason }}</td>
                            <td>{{ $req->created_at->format('Y/m/d') }}</td>

                            {{-- 詳細 → 勤怠詳細画面 --}}
                            <td>
                                <a href="{{ route('attendance.detail', $req->attendance->id) }}" class="detail-link">
                                    詳細
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="empty">表示できるデータがありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
