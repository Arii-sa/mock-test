<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status_id',
        'work_date',
        'work_in',
        'work_out',
    ];

    protected $casts = [
        'work_date' => 'date',
        'work_in' => 'string',
        'work_out' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function corrections()
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    private function toCarbonTime(?string $time)
    {
        if (!$time) return null;

        return Carbon::createFromFormat('H:i:s', $time);
    }

    public function getBreakTotalAttribute()
    {
        $totalSeconds = 0;

        foreach ($this->breaks as $break) {
            $start = $this->toCarbonTime($break->break_start);
            $end   = $this->toCarbonTime($break->break_end);

            if ($start && $end) {
                $totalSeconds += $end->diffInSeconds($start);
            }
        }

        return gmdate('H:i', $totalSeconds);
    }

    public function getWorkTotalAttribute()
    {
        $start = $this->toCarbonTime($this->work_in);
        $end   = $this->toCarbonTime($this->work_out);

        if (!$start || !$end) {
            return '-';
        }

        $workSeconds = $end->diffInSeconds($start);

        // 休憩時間合計
        $breakSeconds = 0;
        foreach ($this->breaks as $break) {
            $bStart = $this->toCarbonTime($break->break_start);
            $bEnd   = $this->toCarbonTime($break->break_end);

            if ($bStart && $bEnd) {
                $breakSeconds += $bEnd->diffInSeconds($bStart);
            }
        }

        $total = $workSeconds - $breakSeconds;

        if ($total < 0) {
            $total = 0;
        }

        return gmdate('H:i', $total);
    }
}
