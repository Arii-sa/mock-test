<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'applications_status_id',
        'reason',
        'request_start_time',
        'request_end_time',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'request_start_time' => 'string',
        'request_end_time'   => 'string',
        'approved_at'        => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applicationStatus()
    {
        return $this->belongsTo(ApplicationStatus::class, 'applications_status_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceCorrectionBreak::class, 'correction_id');
    }
}
