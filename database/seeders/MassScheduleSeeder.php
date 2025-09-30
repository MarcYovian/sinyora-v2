<?php

namespace Database\Seeders;

use App\Models\MassSchedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MassScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MassSchedule::truncate();


        MassSchedule::create([
            'day_of_week' => 0, // 0 = Minggu
            'start_time' => '17:00:00',
            'label' => 'Misa Sore',
            'description' => 'Misa Mingguan'
        ]);
    }
}
