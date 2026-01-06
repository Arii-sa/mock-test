<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceCorrectionBreak;
use App\Models\Attendance;
use App\Models\ApplicationStatus;
use App\Http\Requests\AttendanceCorrection\AttendanceCorrectionRequest;
use Carbon\Carbon;

class AttendanceCorrectionController extends Controller
{

    public function index(Request $request)
    {
        $activeTab = $request->tab === 'approved' ? 'approved' : 'pending';
        $pendingId  = ApplicationStatus::where('name', '承認待ち')->value('id');
        $approvedId = ApplicationStatus::where('name', '承認済み')->value('id');

        $requests = AttendanceCorrection::where('user_id', Auth::id())
            ->when($activeTab === 'pending', fn($q) => $q->where('applications_status_id', $pendingId))
            ->when($activeTab === 'approved', fn($q) => $q->where('applications_status_id', $approvedId))
            ->with(['attendance', 'applicationStatus'])
            ->orderByDesc('id')
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
    public function request(AttendanceCorrectionRequest $request, $idOrDate)
    {
        $userId = Auth::id();

        if (ctype_digit($idOrDate)) {

            $attendance = Attendance::where('user_id', $userId)->find($idOrDate);
            if (!$attendance) {
                abort(404, '指定の勤怠が見つかりません');
            }
        } else {

            $date = Carbon::parse($idOrDate)->format('Y-m-d');
            $attendance = Attendance::firstOrCreate(
                ['user_id' => $userId, 'work_date' => $date],
                ['status_id' => 1]
            );
        }

        $data = $request->validated();

        $correction = AttendanceCorrection::create([
            'attendance_id'          => $attendance->id,
            'user_id'                => $userId,
            'reason'                 => $request->input('reason'),
            'applications_status_id' => ApplicationStatus::where('name', '承認待ち')->first()->id,
            'request_start_time'     => $request->input('work_in'),
            'request_end_time'       => $request->input('work_out'),
        ]);

        foreach ($data['breaks'] ?? [] as $b) {
            if (!empty($b['start']) || !empty($b['end'])) {
                AttendanceCorrectionBreak::create([
                    'correction_id' => $correction->id,
                    'break_start'   => $b['start'] ?? null,
                    'break_end'     => $b['end'] ?? null,
                ]);
            }
        }

        if (!empty($data['breaks_new']['start']) || !empty($data['breaks_new']['end'])) {
            AttendanceCorrectionBreak::create([
                'correction_id' => $correction->id,
                'break_start'   => $data['breaks_new']['start'] ?? null,
                'break_end'     => $data['breaks_new']['end'] ?? null,
            ]);
        }

        return redirect()
            ->route('attendance.detail', $attendance->id)
            ->with('message', '修正申請を作成しました');
    }

}