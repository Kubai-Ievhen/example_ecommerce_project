<?php

use Illuminate\Database\Seeder;

class UsersAdressesTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = \App\User::all();

        foreach ($users as $user){
            if($user->role_id < 5){
                $profile = $user->profile()->first();

                DB::table('users_addresses')->insert([
                    'user_id'           => $user->id,
                    'first_name'        => $user->first_name,
                    'last_name'         => $user->last_name,
                    'city_id'           => $profile->city_id,
                    'city_name'         => $profile->city_name,
                    'state_id'          => $profile->state_id,
                    'state_name'        => $profile->state_name,
                    'country_id'        => $profile->country_id,
                    'country_name'      => $profile->country_name,
                    'street_line_1'     => $profile->street_line_1,
                    'street_line_2'     => $profile->street_line_2,
                    'zipcode'           => $profile->zipcode,
                    'phone'             => $user->phone
                ]);
            }
        }
    }
}
