<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionController;


// ============================
// 認証系（一般ユーザー）
// ============================

// 会員登録
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

// ログイン画面
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// ログアウト
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ============================
// メール認証関係
// ============================

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送しました！');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');


// ============================
// 勤怠システム（認証済ユーザーのみ）
// ============================

Route::middleware(['auth', 'verified'])->group(function () {

    // 出勤登録画面
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.breakIn');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakOut');
    Route::post('/attendance/leave', [AttendanceController::class, 'leave'])->name('attendance.leave');

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
    ->name('attendance.list');

    // 勤怠詳細
    Route::get('/attendance/detail/{idOrDate}', [AttendanceController::class, 'show'])
    ->name('attendance.detail');

    // 勤怠 修正申請（仮保存）
    Route::post('/attendance/detail/{idOrDate}/request', [AttendanceCorrectionController::class, 'request'])
    ->name('attendance.request');

    // 申請一覧
    Route::get('/stamp_correction_request/list', [AttendanceCorrectionController::class, 'index'])
    ->name('attendance_correction.list');

});

require __DIR__.'/admin.php';
