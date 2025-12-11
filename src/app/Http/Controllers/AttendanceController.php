<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Status;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * 勤怠トップ画面
     */
    public function index()
    {
        $user = Auth::user();

        // 今日の勤怠情報を取得
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', Carbon::today())
            ->first();

        // ステータス判定
        $status = 'off'; // デフォルト勤務外
        $statusLabel = '勤務外';

        if ($attendance) {
            $onBreak = $attendance->breaks()
                ->whereNull('break_end')
                ->exists();

            if ($attendance->work_out) {
                $status = 'finished';
                $statusLabel = '退勤済';
            } elseif ($onBreak) {
                $status = 'break';
                $statusLabel = '休憩中';
            } elseif ($attendance->work_in) {
                $status = 'working';
                $statusLabel = '出勤中';
            }
        }

        return view('attendance.index', compact('status', 'statusLabel'));
    }

    /**
     * 出勤処理
     */
    public function start(Request $request)
    {
        $today = Carbon::today();

        // 今日すでに出勤しているか
        $exist = Attendance::where('user_id', Auth::id())
            ->whereDate('work_date', $today)
            ->exists();

        if ($exist) {
            return redirect()->route('attendance.index')
                ->with('error', 'すでに出勤済みです');
        }

        Attendance::create([
            'user_id'   => Auth::id(),
            'work_date' => $today,
            'work_in'   => now()->format('H:i:s'),
            'status_id' => 2,   // 出勤中
        ]);

        return redirect()->route('attendance.index')->with('message', '出勤しました');
    }


    /**
     * 休憩開始
     */
    public function breakIn()
    {
        $attendance = $this->getTodayAttendance();

        if (!$attendance->work_in) {
            return back()->with('error', '出勤前に休憩はできません');
        }

        // すでに休憩中なら不可
        if ($attendance->breaks()->whereNull('break_end')->exists()) {
            return back()->with('error', 'すでに休憩中です');
        }

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start'   => now()->format('H:i:s'),
        ]);

        // ステータス：休憩中（3）
        $attendance->update(['status_id' => 3]);


        return redirect()->route('attendance.index')->with('message', '休憩を開始しました');
    }

    /**
     * 休憩終了
     */
    public function breakOut()
    {
        $attendance = $this->getTodayAttendance();
        $break = $attendance->breaks()
            ->whereNull('break_end')
            ->first();

            if (!$break) {
                return redirect()->route('attendance.index')->with('error', '休憩中ではありません');
            }

            $break->update(['break_end' => now()->format('H:i:s')]);

            // ステータス：出勤中（2）
            $attendance->update(['status_id' => 2]);
        return redirect()->route('attendance.index')->with('message', '休憩を終了しました');
    }

    /**
    * 退勤処理
    */
    public function leave()
    {
        $attendance = $this->getTodayAttendance();

        // すでに退勤済みなら不可
        if ($attendance->work_out) {
            return redirect()->route('attendance.index')->with('error', 'すでに退勤済みです');
        }

        $attendance->update([
            'work_out'  => now()->format('H:i:s'),
            'status_id' => 4, // 退勤済
        ]);

        return redirect()->route('attendance.index')->with('message', 'お疲れ様でした。');
    }

    /**
     * 今日の勤怠情報を取得
     */
    private function getTodayAttendance()
    {
        return Attendance::where('user_id', Auth::id())
            ->whereDate('work_date', Carbon::today())
            ->firstOrFail();
    }

    /**
     * 勤怠一覧（過去の勤怠記録を表示）
     */
    public function list(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth   = Carbon::parse($month)->endOfMonth();

        $dates = [];
        $day = $startOfMonth->copy();
        while ($day->lte($endOfMonth)) {
            $dates[] = $day->copy();
            $day->addDay();
        }

        $attendances = Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->work_date->format('Y-m-d') => $item
                ];
            });

        return view('attendance.list', [
            'dates' => $dates,
            'attendances' => $attendances,
            'month' => $month,
        ]);
    }

    public function show($idOrDate)
    {
        $userId = Auth::id();

        // ----------------------------------------
        // 数字 = Attendance ID として検索
        // ----------------------------------------
        if (ctype_digit($idOrDate)) {

            $attendance = Attendance::with('breaks')
                ->where('user_id', $userId)
                ->findOrFail($idOrDate);

        } else {

            // ----------------------------------------
            // 日付指定の場合
            // ----------------------------------------
            $date = Carbon::parse($idOrDate)->format('Y-m-d');

            $attendance = Attendance::with('breaks')
                ->where('user_id', $userId)
                ->whereDate('work_date', $date)
                ->first();

            // 出勤していない → 新規モデル（id=null）を返す
            if (!$attendance) {
                $attendance = new Attendance([
                    'user_id'   => $userId,
                    'work_date' => $date,
                    'status_id' => 1, // 勤務外
                ]);

                // 空の休憩をセット
                $attendance->setRelation('breaks', collect());
            }
        }

        // 申請履歴
        $latestCorrection = \App\Models\AttendanceCorrection::where('attendance_id', $attendance->id)
            ->orderBy('id', 'desc')
            ->first();

        return view('attendance.detail', compact('attendance', 'latestCorrection'));
    }


}