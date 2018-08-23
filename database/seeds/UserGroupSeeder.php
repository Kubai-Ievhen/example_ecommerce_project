<?php

use Illuminate\Database\Seeder;

class UserGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $base_groups = \App\BaseGroup::all();

        foreach ($base_groups as $base_group){
            DB::table('user_groups')->insert(['title' => $base_group->title, 'base_group_id'=>$base_group->id]);
        }
    }
}
