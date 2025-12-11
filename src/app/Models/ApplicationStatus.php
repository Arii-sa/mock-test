<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationStatus extends Model
{
    use HasFactory;

    protected $table = 'applications_statuses';

    protected $fillable = [
        'name',
    ];

    public function attendanceCorrections()
    {
        return $this->hasMany(AttendanceCorrection::class, 'applications_status_id');
    }
}
