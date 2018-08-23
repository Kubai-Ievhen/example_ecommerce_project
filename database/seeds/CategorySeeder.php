<?php

use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = ['Visiting Cards', 'Envelops', 'Letter Head', 'Brochures', 'File', 'Sticker', 'Flyers',
            'Dangler', 'Banners', 'Posters', 'Wedding Cards', 'Invitation Card'];

        foreach ($categories as $category) {
            DB::table('categories')->insert(['name' => $category, 'active' => true]);
        }
    }
}
