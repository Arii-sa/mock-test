<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceCorrection;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceCorrectionController extends Controller
{
    // 全スタッフ申請一覧
    public function index(Request $request)
    {
        $activeTab = $request->status === 'approved' ? 'approved' : 'pending';

        $query = AttendanceCorrection::with(['user', 'attendance', 'applicationStatus']);

        $query->where(
            'applications_status_id',
            $activeTab === 'pending' ? 1 : 2
        );

        $corrections = $query->latest()->paginate(20);

        return view('admin.attendance.request', compact('corrections', 'activeTab'));
    }

    // 申請詳細
    public function show($id)
    {
        $correction = AttendanceCorrection::with([
            'user',
            'attendance',
            'applicationStatus',
            'breaks',
        ])->findOrFail($id);

        return view('admin.attendance.request_detail', compact('correction'));
    }

    // ★ 申請承認処理
    public function approve(Request $request, $id)
    {
        DB::transaction(function () use ($id) {

            $correction = AttendanceCorrection::with([
                'attendance',
                'breaks',
            ])->findOrFail($id);

            $attendance = $correction->attendance;

            $attendance->update([
                'work_in'  => $correction->request_start_time,
                'work_out' => $correction->request_end_time,
            ]);

            BreakTime::where('attendance_id', $attendance->id)->delete();

            foreach ($correction->breaks as $break) {
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start'   => $break->break_start,
                    'break_end'     => $break->break_end,
                ]);
            }

            $correction->update([
                'applications_status_id' => 2,
                'approved_at' => Carbon::now(),
                'approved_by' => Auth::id(),
            ]);
        });

        return redirect()->back()->with('message', '申請を承認しました');
    }
}
