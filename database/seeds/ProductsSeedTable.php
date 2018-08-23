<?php

use Illuminate\Database\Seeder;

class ProductsSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        for($i=0; $i<100; $i++){
            DB::table('products')->insert([
                'title' => str_random(10),
                'price' => rand(1,999),
                'image_id' => 0,
                'category_id' => rand(1,13)
            ]);
        }
    }
}
