<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'correction_id',
        'break_start',
        'break_end',
    ];

    protected $casts = [
        'break_start' => 'string',
        'break_end'   => 'string',
    ];

    public function correction()
    {
        return $this->belongsTo(AttendanceCorrection::class, 'correction_id');
    }
}
