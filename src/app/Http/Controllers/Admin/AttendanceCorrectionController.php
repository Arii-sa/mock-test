<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceCorrection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceCorrectionController extends Controller
{
    // 全スタッフ申請一覧
    public function index(Request $request)
    {
        // タブ種別判定（デフォは承認待ち）
        $activeTab = $request->status === 'approved' ? 'approved' : 'pending';

        $query = AttendanceCorrection::with(['user', 'attendance', 'applicationStatus']);

        if ($activeTab === 'pending') {
            $query->where('applications_status_id', 1);
        } else {
            $query->where('applications_status_id', 2);
        }

        $corrections = $query->latest()->paginate(20);

        return view('admin.attendance.request', compact('corrections', 'activeTab'));
    }

    //申請詳細
    public function show($id)
    {
        $correction = AttendanceCorrection::with([
            'user',
            'attendance',
            'applicationStatus',
            'breaks'
            ])
            ->findOrFail($id);

        return view('admin.attendance.request_detail', compact('correction'));
    }

    // 申請承認処理
    public function approve(Request $request, $id)
    {
        $correction = AttendanceCorrection::findOrFail($id);

        $correction->update([
            'applications_status_id' => 2, // 承認済み
            'approved_at' => Carbon::now(),
            'approved_by' => Auth::id(),
        ]);

        return redirect()->back()->with('message', '申請を承認しました');
    }
}
