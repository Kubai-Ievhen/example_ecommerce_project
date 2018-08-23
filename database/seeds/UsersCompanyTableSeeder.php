<?php

use Illuminate\Database\Seeder;

class UsersCompanyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = \App\User::all();
        $max = \App\CompanyType::all()->count();

        foreach ($users as $user){
            if($user->group_id < 5 && $user->group_id > 1){
                DB::table('organization_datas')->insert([
                    'user_id' => $user->id,
                    'organization_name' => "\"$user->first_name $user->last_name LTD\"",
                    'phone' => rand(1000000000,9999999999),
                    'company_type_id' => rand(1,$max),
                    'authorize_person' => "$user->first_name $user->last_name",
                ]);
            }
        }
    }
}
