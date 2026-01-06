<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\AttendanceCorrectionController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| 管理者専用ルート
|
*/
Route::prefix('admin')->name('admin.')->group(function () {
    // 管理者ログイン
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // 管理者認証が必要なルート
    Route::middleware(['auth:admin'])->group(function () {

        // 勤怠一覧
        Route::get('/attendance/list', [AttendanceController::class, 'index'])
            ->name('attendance.list');

        // 勤怠詳細
        Route::get('/attendance/{idOrDate}', [AttendanceController::class, 'show'])
            ->name('attendance.detail');
        // 勤怠更新（管理者）
        Route::patch('/attendance/{idOrDate}', [AttendanceController::class, 'update'])
        ->name('attendance.update');


        // スタッフ別勤怠一覧
        Route::get('/attendance/staff/{id}', [AttendanceController::class, 'showByStaff'])
            ->name('attendance.staff');
        Route::get('/attendance/staff/{id}/csv', [AttendanceController::class, 'exportCsv'])
            ->name('attendance.staff.csv');

        // スタッフ一覧
        Route::get('/staff/list', [StaffController::class, 'index'])
            ->name('staff.list');

        // 勤怠修正申請一覧
        Route::get('/stamp_correction_request/list', [AttendanceCorrectionController::class, 'index'])
            ->name('attendance_correction.request');

        // 修正申請の詳細画面
        Route::get('/stamp_correction_request/detail/{id}',
            [AttendanceCorrectionController::class, 'show'])
            ->name('attendance_correction.detail');

        // 修正申請承認
        Route::match(['post', 'patch'], '/stamp_correction_request/approve/{attendance_correct_request_id}', 
            [AttendanceCorrectionController::class, 'approve'])
            ->name('attendance_correction.approve');
    });
});

