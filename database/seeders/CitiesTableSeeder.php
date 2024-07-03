<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cities')->insert([
            ['name' => 'New York', 'state' => 'NY', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Los Angeles', 'state' => 'CA', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Chicago', 'state' => 'IL', 'created_at' => now(), 'updated_at' => now()],
            // Add more cities as needed
        ]);
    }
}
