<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $now = Carbon::now();
        DB::table('statuses')->insert([
            ['name' => '勤務外', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '出勤中', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '休憩中', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '退勤済', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
