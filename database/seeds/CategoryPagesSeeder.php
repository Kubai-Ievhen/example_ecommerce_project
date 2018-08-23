<?php

use Illuminate\Database\Seeder;

class CategoryPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('pages_categories')->insert(['name' => 'Cards', 'parent_id' => '0']);
        DB::table('pages_categories')->insert(['name' => 'Caps', 'parent_id' => '0']);
        DB::table('pages_categories')->insert(['name' => 'Book', 'parent_id' => '0']);
        DB::table('pages_categories')->insert(['name' => 'Business Cards', 'parent_id' => '1']);
        DB::table('pages_categories')->insert(['name' => 'Logo Cards', 'parent_id' => '1']);
        DB::table('pages_categories')->insert(['name' => 'Roominess 0.5l', 'parent_id' => '2']);
        DB::table('pages_categories')->insert(['name' => 'Roominess 0.25l', 'parent_id' => '2']);
        DB::table('pages_categories')->insert(['name' => 'Roominess 0.35l', 'parent_id' => '2']);
        DB::table('pages_categories')->insert(['name' => 'Roominess 1l', 'parent_id' => '2']);
        DB::table('pages_categories')->insert(['name' => 'Novel', 'parent_id' => '3']);
        DB::table('pages_categories')->insert(['name' => 'Detective', 'parent_id' => '3']);
        DB::table('pages_categories')->insert(['name' => 'Children\'s literature', 'parent_id' => '3']);
        DB::table('pages_categories')->insert(['name' => 'Other', 'parent_id' => '3']);
    }
}
