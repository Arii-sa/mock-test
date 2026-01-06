<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;


class AttendanceController extends Controller
{
    /**
     * =========================
     * 全スタッフ 日次勤怠一覧
     * =========================
     */
    public function index(Request $request)
    {
        $date = $request->date
            ? Carbon::parse($request->date)
            : Carbon::today();

        // 確定勤怠
        $attendances = Attendance::whereDate('work_date', $date)
            ->get()
            ->keyBy('user_id');

        // 申請中の修正
        $corrections = AttendanceCorrection::with('breaks')
            ->where('applications_status_id', 1)
            ->whereHas('attendance', fn($q) => $q->whereDate('work_date', $date))
            ->get()
            ->keyBy(fn($c) => $c->attendance->user_id);

        // 出勤済み or 申請中ユーザーの ID をまとめる
        $userIds = collect(array_merge(
            $attendances->keys()->toArray(),
            $corrections->keys()->toArray()
        ))->unique();

        // 該当ユーザーだけ取得
        $users = User::whereIn('id', $userIds)->orderBy('id')->get();

        // 表示用配列
        $rows = [];
        foreach ($users as $user) {
            $attendance = $attendances[$user->id] ?? null;
            $correction = $corrections[$user->id] ?? null;

            if ($correction) {
                $rows[] = [
                    'user'     => $user,
                    'work_in'  => $correction->request_start_time,
                    'work_out' => $correction->request_end_time,
                    'break'    => null,
                    'total'    => null,
                    'status'   => 'pending',
                    'id'       => $attendance?->id,
                ];
            } elseif ($attendance) {
                $rows[] = [
                    'user'     => $user,
                    'work_in'  => $attendance->work_in,
                    'work_out' => $attendance->work_out,
                    'break'    => $attendance->break_total,
                    'total'    => $attendance->work_total,
                    'status'   => 'normal',
                    'id'       => $attendance->id,
                ];
            }
        }

        return view('admin.attendance.index', compact('date', 'rows'));
    }
    /**
     * =========================
     * 勤怠詳細
     * =========================
     */
    public function show($idOrDate)
    {
        // ★ id（数値）か日付かを判定
        if (ctype_digit($idOrDate)) {

            $attendance = Attendance::with(['user', 'breaks'])
                ->findOrFail($idOrDate);

            $date = $attendance->work_date;

        } else {

            $date = Carbon::parse($idOrDate)->format('Y-m-d');

            if (!request()->filled('user_id')) {
                abort(404);
            }

            $attendance = Attendance::with(['user', 'breaks'])
                ->whereDate('work_date', $date)
                ->where('user_id', request('user_id'))
                ->first();

            // ★ 勤怠が無い日は「仮オブジェクト」を作る（保存しない）
            if (!$attendance) {
                $attendance = new Attendance([
                    'work_date' => $date,
                    'user_id'   => request('user_id'),
                ]);

                $attendance->setRelation('user', User::findOrFail(request('user_id')));
                $attendance->setRelation('breaks', collect());
            }
        }

        // ★ 最新の修正申請（あれば）
        $latestCorrection = AttendanceCorrection::with('breaks')
            ->where('attendance_id', $attendance->id)
            ->latest()
            ->first();

        return view('admin.attendance.detail', compact(
            'attendance',
            'latestCorrection',
            'date'
        ));
    }

    public function update(AdminAttendanceUpdateRequest $request,$idOrDate)
    {
        DB::transaction(function () use ($request, $idOrDate, &$attendance) 
        {

            if (ctype_digit($idOrDate)) {

                // 既存勤怠
                $attendance = Attendance::with('breaks')->findOrFail($idOrDate);

                // ★ 承認待ちチェック（既存勤怠のみ）
                $pending = AttendanceCorrection::where('attendance_id', $attendance->id)
                    ->where('applications_status_id', 1)
                    ->exists();

                if ($pending) {
                    abort(403, '承認待ちのため修正できません。');
                }

            } else {

                // 未登録日の場合 → 新規作成
                $attendance = Attendance::create([
                    'user_id'   => $request->user_id,
                    'work_date' => Carbon::parse($idOrDate),
                    'work_in'   => $request->work_in,
                    'work_out'  => $request->work_out,
                    'note'      => $request->reason,


                    'status_id' => 1,
                ]);
            }

            $attendance->update([
                'work_in'  => $request->work_in,
                'work_out' => $request->work_out,
                'note'     => $request->reason,
            ]);

            BreakTime::where('attendance_id', $attendance->id)->delete();

            // 再登録
            foreach ($request->breaks ?? [] as $b) {
                if (!empty($b['start']) || !empty($b['end'])) {
                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start'   => $b['start'] ?? null,
                        'break_end'     => $b['end'] ?? null,
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.attendance.detail', $attendance->id)
            ->with('message', '勤怠を修正しました');
    }


    /**
     * =========================
     * スタッフ別 月次勤怠一覧
     * =========================
     */
    public function showByStaff(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $month = $request->month
            ? Carbon::parse($request->month . '-01')
            : Carbon::now()->startOfMonth();

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        // ▼ 月の日付一覧
        $dates = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dates[] = $d->copy();
        }

        // ▼ 勤怠
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [$start, $end])
            ->get()
            ->keyBy(fn ($a) => $a->work_date->format('Y-m-d'));

        // ▼ 修正申請（申請中）
        $corrections = AttendanceCorrection::where('user_id', $id)
            ->where('applications_status_id', 1)
            ->whereHas('attendance', function ($q) use ($start, $end) {
                $q->whereBetween('work_date', [$start, $end]);
            })
            ->get()
            ->keyBy(fn ($c) => $c->attendance->work_date->format('Y-m-d'));

        return view('admin.attendance.list', compact(
            'user',
            'dates',
            'attendances',
            'corrections',
            'month'
        ));
    }

    public function exportCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // 月指定
        $month = $request->month
            ? Carbon::parse($request->month . '-01')
            : Carbon::now()->startOfMonth();

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        // 勤怠取得
        $attendances = Attendance::with('breaks')
            ->where('user_id', $id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->get();

        $fileName = "attendance_{$user->id}_{$month->format('Y_m')}.csv";

        return response()->streamDownload(function () use ($attendances) {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                '日付',
                '出勤時間',
                '退勤時間',
                '休憩時間（分）',
                '勤務時間（分）',
                '備考',
            ]);

            foreach ($attendances as $a) {
                fputcsv($handle, [
                    $a->work_date->format('Y-m-d'),
                    $a->work_in,
                    $a->work_out,
                    $a->break_total,
                    $a->work_total,
                    $a->note,
                ]);
            }

            fclose($handle);

        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

}