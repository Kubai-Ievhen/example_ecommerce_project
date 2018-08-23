<?php

use Illuminate\Database\Seeder;

class TimeDispatchMessagesSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('time_dispatch_messages')->insert([
            'daily_hour' => rand(0,23),
            'weekly_hour' => rand(0,23),
            'weekly_day' => rand(0,6),
            'monthly_hour' => rand(0,23),
            'monthly_day' => rand(0,31),
            'annually_hour' => rand(0,23),
            'annually_day' => rand(0,31),
            'annually_months' => rand(0,11),
        ]);
    }
}
