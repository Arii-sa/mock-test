<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApplicationStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $now = Carbon::now();
        DB::table('applications_statuses')->insert([
            ['name' => '承認待ち', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '承認済み', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
