<?php

use Illuminate\Database\Seeder;

class InputMessageTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users_id = \App\User::where('id','>',0)->pluck('id')->toArray();

        for($i=0;$i<10;$i++){
            $id = array_rand($users_id);
            $user = \App\User::find($id);
            DB::table('input_messages')->insert([
                'sender_user_id' => $id,
                'title' => "$user->first_name $user->last_name Message",
                'content' => 'Sam message for you',
                'is_read' => rand(0,1),
                'is_important' => rand(0,1),
                'is_notification' => rand(0,1),
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            ]);
        }
    }
}
