<?php

use Illuminate\Database\Seeder;

class ActivitiesUsersTypeSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('activities_users_types')->insert(['name' => 'User registration']);
        DB::table('activities_users_types')->insert(['name' => 'Delete user']);
        DB::table('activities_users_types')->insert(['name' => 'User has registered']);
        DB::table('activities_users_types')->insert(['name' => 'User retired']);
        DB::table('activities_users_types')->insert(['name' => 'User approval']);
        DB::table('activities_users_types')->insert(['name' => 'User\'s phone is verified']);
        DB::table('activities_users_types')->insert(['name' => 'User\'s email is verified']);
        DB::table('activities_users_types')->insert(['name' => 'The user has placed an order']);
        DB::table('activities_users_types')->insert(['name' => 'The Order has been completed and shipped']);
        DB::table('activities_users_types')->insert(['name' => 'The user has received the order']);
        DB::table('activities_users_types')->insert(['name' => 'The designer has added his product']);
        DB::table('activities_users_types')->insert(['name' => 'The designer\'s product is approved']);
    }
}
