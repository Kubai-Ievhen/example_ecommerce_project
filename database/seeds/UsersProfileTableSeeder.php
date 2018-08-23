<?php

use Illuminate\Database\Seeder;

class UsersProfileTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = \App\User::all();

        $Geo = new \App\Http\Controllers\GeolocationController();



        foreach ($users as $user){
            if($user->role_id < 5){
                $company_id = 0;
                if($user->role_id > 1){
                    $company = \App\OrganizationData::where('user_id', $user->id)->first();
                    $company_id = $company->id;
                }

                $countrus = $Geo->getCountrys();
                $countru = $countrus[array_rand($countrus)];
                $states = !empty($countru['code'])?$Geo->getStates($countru['code']):'';
                $state = !empty($states)?$states[array_rand($states)]:'';
                $cities = '';
                $citie = !empty($cities)?$cities[array_rand($cities)]:'';

                DB::table('user_profiles')->insert([
                    'user_id'           => $user->id,
                    'payment_options'   => 1,
                    'terms_conditions'  => rand(0,1),
                    'company_id'        => $company_id,
                    'document'          => 0,
                    'city_id'           => !empty($citie)?$citie['code']:'',
                    'city_name'         => !empty($citie)?$citie['name']:'',
                    'state_id'          => !empty($state)?$state['code']:'',
                    'state_name'        => !empty($state)?$state['name']:'',
                    'country_id'        => $countru['code'],
                    'country_name'      => $countru['name'],
                    'zipcode'           => !empty($citie)?$citie['code']:!empty($state)?$state['code']:'',
                ]);
            }
        }
    }
}
