<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceCorrection;
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

        $attendance = Attendance::with('status', 'breaks')
        ->where('user_id', $user->id)
        ->whereDate('work_date', Carbon::today())
        ->first();

        $status = 'off';
        $statusLabel = '勤務外';

        if ($attendance && $attendance->status) {
            switch ($attendance->status->name) {
                case '出勤中':
                    $status = 'working';
                    $statusLabel = '出勤中';
                    break;

                case '休憩中':
                    $status = 'break';
                    $statusLabel = '休憩中';
                    break;

                case '退勤済':
                    $status = 'finished';
                    $statusLabel = '退勤済';
                    break;
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
            'status_id' => Status::where('name', '出勤中')->value('id'),   // 出勤中
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

        if ($attendance->breaks()->whereNull('break_end')->exists()) {
            return back()->with('error', 'すでに休憩中です');
        }

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start'   => now()->format('H:i:s'),
        ]);

        $attendance->update([
            'status_id' => Status::where('name', '休憩中')->value('id'),
        ]);


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

            $attendance->update([
                'status_id' => Status::where('name', '出勤中')->value('id'),
            ]);

        return redirect()->route('attendance.index')->with('message', '休憩を終了しました');
    }

    /**
    * 退勤処理
    */
    public function leave()
    {
        $attendance = $this->getTodayAttendance();

        if ($attendance->work_out) {
            return redirect()->route('attendance.index')->with('error', 'すでに退勤済みです');
        }

        $attendance->update([
            'work_out'  => now()->format('H:i:s'),
            'status_id' => Status::where('name', '退勤済')->value('id'),
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

        $corrections = \App\Models\AttendanceCorrection::with('breaks', 'attendance')
        ->where('user_id', Auth::id())
        ->whereHas('attendance', function($q) use ($startOfMonth, $endOfMonth) {
            $q->whereBetween('work_date', [$startOfMonth, $endOfMonth]);
        })
        ->get()
        ->mapWithKeys(function($item) {
            return [$item->attendance->work_date->format('Y-m-d') => $item];
        });


        return view('attendance.list', [
            'dates' => $dates,
            'attendances' => $attendances,
            'corrections' => $corrections,
            'month' => $month,
        ]);
    }

    public function show($idOrDate)
    {
        $userId = Auth::id();

        if (ctype_digit($idOrDate)) {
            $attendance = Attendance::find($idOrDate);
        } else {
            $date = Carbon::parse($idOrDate)->format('Y-m-d');
            $attendance = Attendance::where('user_id', $userId)
                ->whereDate('work_date', $date)
                ->first();
        }

        if (!$attendance) {
            $attendance = Attendance::make(['work_date' => $date ?? now()->format('Y-m-d')]);

            $attendance->setRelation('breaks', collect());
        }

        // 最新の修正申請
        $latestCorrection = $attendance->id
            ? AttendanceCorrection::where('attendance_id', $attendance->id)
                ->with('breaks')
                ->orderBy('id', 'desc')
                ->first()
            : null;

        return view('attendance.detail', compact('attendance', 'latestCorrection'));
    }

}