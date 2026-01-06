<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDatetimeToTimeOnAttendanceTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // attendances
        Schema::table('attendances', function (Blueprint $table) {
            $table->time('work_in')->nullable()->change();
            $table->time('work_out')->nullable()->change();
        });

        // breaks
        Schema::table('breaks', function (Blueprint $table) {
            $table->time('break_start')->nullable(false)->change();
            $table->time('break_end')->nullable()->change();
        });

        // attendance_corrections
        Schema::table('attendance_corrections', function (Blueprint $table) {
            $table->time('request_start_time')->nullable()->change();
            $table->time('request_end_time')->nullable()->change();
        });

        // attendance_correction_breaks
        Schema::table('attendance_correction_breaks', function (Blueprint $table) {
            $table->time('break_start')->nullable()->change();
            $table->time('break_end')->nullable()->change();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // attendances
        Schema::table('attendances', function (Blueprint $table) {
            $table->dateTime('work_in')->nullable()->change();
            $table->dateTime('work_out')->nullable()->change();
        });

        Schema::table('breaks', function (Blueprint $table) {
            $table->dateTime('break_start')->nullable(false)->change();
            $table->dateTime('break_end')->nullable()->change();
        });

        Schema::table('attendance_corrections', function (Blueprint $table) {
            $table->dateTime('request_start_time')->nullable()->change();
            $table->dateTime('request_end_time')->nullable()->change();
        });

        Schema::table('attendance_correction_breaks', function (Blueprint $table) {
            $table->dateTime('break_start')->nullable()->change();
            $table->dateTime('break_end')->nullable()->change();
        });
    }
}
