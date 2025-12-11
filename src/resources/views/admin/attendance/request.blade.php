@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/request.css') }}">
@endsection

@section('content')
<div class="request-list-container">

    <h2 class="page-title">申請一覧</h2>

    {{-- タブ切替 --}}
    <div class="tabs">
        <a href="?status=pending" class="tab {{ $activeTab === 'pending' ? 'active' : '' }}">
            承認待ち
        </a>
        <a href="?status=approved" class="tab {{ $activeTab === 'approved' ? 'active' : '' }}">
            承認済み
        </a>
    </div>

    <table class="request-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日</th>
                <th>申請日</th>
                <th>理由</th>
                <th>詳細</th>
            </tr>
        </thead>

        <tbody>
            @forelse($corrections as $item)
                <tr>
                    <td>{{ $item->applicationStatus->status }}</td>
                    <td>{{ $item->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->attendance->work_date)->format('Y/m/d') }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('Y/m/d') }}</td>
                    <td>{{ $item->reason }}</td>
                    <td>
                        <a href="{{ route('admin.attendance_correction.detail', $item->id) }}" class="detail-link">
                            詳細
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty-msg">データがありません</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ページネーション --}}
    <div class="pagination-area">
        {{ $corrections->links('pagination::bootstrap-4') }}
    </div>

</div>
@endsection
