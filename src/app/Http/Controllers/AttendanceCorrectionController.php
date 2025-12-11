<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceCorrectionBreak;
use App\Models\Attendance;
use App\Models\ApplicationStatus;
use Carbon\Carbon;

class AttendanceCorrectionController extends Controller
{

    public function index(Request $request)
    {
        $activeTab = $request->tab === 'approved' ? 'approved' : 'pending';

        $requests = AttendanceCorrection::where('user_id', Auth::id())
            ->when($activeTab === 'pending', function ($q) {
                $q->where('applications_status_id', 1); // 承認待ち
            })
            ->when($activeTab === 'approved', function ($q) {
                $q->where('applications_status_id', 2); // 承認済み
            })
            ->with(['attendance', 'applicationStatus'])
            ->orderBy('id', 'desc')
            ->get();

        return view('attendance.request', compact('requests', 'activeTab'));
    }

    // 新規修正申請作成フォーム
    public function create($attendance_id)
    {
        $attendance = Attendance::findOrFail($attendance_id);
        return view('attendance_correction.create', compact('attendance'));
    }

    private function extractTime($value)
    {
        if (empty($value)) return null;

        if (is_array($value)) {
            $value = end($value);
        }

        $value = trim($value);

        if (preg_match('/(\d{1,2}:\d{2})$/', $value, $m)) {
            return $m[1] . ':00';
        }

        return null;
    }


    private function extractDate($value)
    {
        if (empty($value)) return null;

        if (is_array($value)) {
            $value = reset($value);
        }

        $value = trim($value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value, $m)) {
            return $m[0];
        }

        return null;
    }



    // 申請送信
    public function request(Request $request, $idOrDate)
    {
        $userId = Auth::id();

        // ===============================
        // ① attendance 登録 or 取得
        // ===============================
        if (ctype_digit($idOrDate)) {

            $attendance = Attendance::where('user_id', $userId)
                ->findOrFail($idOrDate);

            $date = $this->extractDate($attendance->work_date);

        } else {

            $date = $this->extractDate($idOrDate);

            $attendance = Attendance::create([
                'user_id'   => $userId,
                'work_date' => $date,
                'status_id' => 2,
            ]);
        }


        // ===============================
        // ② 修正申請のメイン作成
        // ===============================
        $correction = AttendanceCorrection::create([
            'attendance_id'          => $attendance->id,
            'user_id'                => $userId,
            'reason'                 => $request->reason,
            'applications_status_id' => 1,     // 承認待ち
            'request_start_time'     => $request->work_in,   // ★修正後
            'request_end_time'       => $request->work_out,  // ★修正後
        ]);


        // ===============================
        // ③ 修正後の休憩（複数）を保存
        // ===============================
        if ($request->breaks) {
            foreach ($request->breaks as $b) {

                $start = $b['start'] ?? null;
                $end   = $b['end']   ?? null;

                if ($start || $end) {
                    AttendanceCorrectionBreak::create([
                        'correction_id' => $correction->id,
                        'break_start'   => $start,
                        'break_end'     => $end,
                    ]);
                }
            }
        }

        // ===============================
        // ④ 追加休憩（1件追加用）
        // ===============================
        if (!empty($request->breaks_new['start']) || !empty($request->breaks_new['end'])) {

            AttendanceCorrectionBreak::create([
                'correction_id' => $correction->id,
                'break_start'   => $request->breaks_new['start'],
                'break_end'     => $request->breaks_new['end'],
            ]);
        }


        return redirect()
            ->route('attendance.detail', $attendance->id)
            ->with('message', '修正申請を提出しました');
    }
}