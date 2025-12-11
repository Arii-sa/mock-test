<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 全スタッフ勤怠一覧
    public function index(Request $request)
    {
        // ① 日付取得（指定なければ今日）
        $date = $request->date
            ? Carbon::parse($request->date)
            : Carbon::today();

        // ② ユーザー側の attendances テーブルから該当日を取得
        $attendances = Attendance::with('user')
            ->whereDate('work_date', $date->format('Y-m-d')) // ← 修正ポイント！
            ->orderBy('user_id')
            ->get();

        // ③ Blade へ
        return view('admin.attendance.index', [
            'date' => $date,
            'attendances' => $attendances,
        ]);
    }


    // 特定勤怠詳細
    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breaks', 'status'])->findOrFail($id);

        // ★ 最新の修正申請 取得（これが必要）
        $latestCorrection = \App\Models\AttendanceCorrection::where('attendance_id', $attendance->id)
            ->latest()
            ->first();

        return view('admin.attendance.detail', [
            'attendance' => $attendance,
            'latestCorrection' => $latestCorrection,
        ]);
    }


    // スタッフ別勤怠一覧
    public function showByStaff(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // 現在の月（指定がなければ今月）
        $currentMonth = $request->month
            ? Carbon::parse($request->month . '-01')
            : Carbon::now()->startOfMonth();

        // 月初と月末
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth   = $currentMonth->copy()->endOfMonth();

        // 指定月の勤怠
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->orderBy('work_date', 'asc')
            ->paginate(20);


        return view('admin.attendance.list', [
            'user' => $user,
            'attendances' => $attendances,
            'currentMonth' => $currentMonth,
        ]);
    }

    public function exportCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // 対象月
        $month = $request->month
            ? Carbon::parse($request->month . '-01')
            : Carbon::now()->startOfMonth();

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        // 勤怠データ取得
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date', 'asc')
            ->get();

        // CSV 内容作成
        $csvHeader = [
            '日付', '出勤', '退勤', '休憩', '勤務時間'
        ];

        $csvData = [];

        foreach ($attendances as $attendance) {
            $csvData[] = [
                $attendance->work_date,
                $attendance->clock_in,
                $attendance->clock_out,
                $attendance->break_total,
                $attendance->work_total,
            ];
        }

        // 出力用のファイル名
        $fileName = $user->name . '_' . $month->format('Y_m') . '_attendance.csv';

        // CSV 生成
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $csvHeader);

        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csvOutput = stream_get_contents($handle);
        fclose($handle);

        return Response::make($csvOutput, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }


}
