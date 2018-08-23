<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('super2017Admin'),
            'phone' => 0,
            'group_id' => 6,
            'approval' => 1,
            'subscribe' => 1,
        ]);

        $firstNames = ['Emma', 'Olivia', 'Sofia', 'Isabel', 'Ava', 'Mia', 'Emily', 'Abigail', 'Madison', 'Charlotte',
            'Noah', 'Liam', 'Mason', 'Jacob', 'William', 'Ethane', 'Michael', 'Alexander', 'James', 'Daniel', 'Anastasia',
            'Maria', 'Daria', 'Anna', 'Elizabeth', 'Pauline', 'Victoria', 'Catherine', 'Alexander', 'Maksim', 'Ivan',
            'Artem', 'Dmitriy', 'Nikita', 'Michael', 'Daniel', 'Egor', 'Andrei'];

        $lastNames = ['Smith', 'Johnson', 'Williams', 'Jones', 'Brown', 'Davis', 'Miller', 'Wilson', 'Moore', 'Taylor',
            'Anderson', 'Thomas', 'Jackson', 'White', 'Harris', 'Martin', 'Thompson', 'Garcia', 'Martinez', 'Robinson',
            'Clark', 'Rodriguez', 'Lewis', 'Lee', 'Walker', 'Hall', 'Allen', 'Young', 'Hernandez', 'King', 'Wright',
            'Lopez', 'Hill', 'Scott', 'Green', 'Adams', 'Baker', 'Gonzalez', 'Nelson', 'Carter', 'Mitchell', 'Perez',
            'Roberts', 'Turner', 'Phillips', 'Campbell', 'Parker', 'Evans', 'Edwards', 'Collins'];

        for ($i = 0; $i < 100; $i++) {
            $fn = $firstNames[array_rand($firstNames)];
            $ln = $lastNames[array_rand($lastNames)];
            DB::table('users')->insert([
                'first_name' => $fn,
                'last_name' => $ln,
                'email' => $ln . '_' . $i . '@gmail.com',
                'password' => bcrypt('secret'),
                'phone' => rand(1000000000, 9999999999),
                'group_id' => rand(1, 4),
                'approval' => rand(0, 1),
                'subscribe' => rand(0, 1),
            ]);
        }

        $fn = $firstNames[array_rand($firstNames)];
        $ln = $lastNames[array_rand($lastNames)];

        DB::table('users')->insert([
            'first_name' => $fn,
            'last_name' => $ln,
            'email' => $ln . '_admin@gmail.com',
            'password' => bcrypt('secret'),
            'phone' => rand(1000000000, 9999999999),
            'group_id' => 5,
            'approval' => rand(0, 1),
            'subscribe' => rand(0, 1),
        ]);
    }
}
