<?php

use Illuminate\Database\Seeder;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vendors = \App\User::where('group_id', 3)->get();
        $vendors_id = [];
        foreach ($vendors as $vendor){
            $vendors_id[]=$vendor->id;
        }

        $users = \App\User::where('group_id', 1)->orWhere('group_id', 2)->get();
        $users_id = [];
        foreach ($users as $user){
            $users_id[]=$user->id;
        }

        for($i=0; $i<30; $i++){
            DB::table('orders')->insert([
                'user_id' => array_rand($users_id),
                'vendor_id' => $i%5?array_rand($vendors_id):'0',
                'product_id' => $i,
                'execution_status' => $i%5?rand(1,3):'0'
            ]);
        }

    }
}
