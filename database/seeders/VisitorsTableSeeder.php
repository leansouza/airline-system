<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VisitorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('visitors')->insert([
            [
                'name' => 'John Doe',
                'cpf' => '12345678901',
                'email' => 'john.doe@example.com',
                'birthdate' => '1990-01-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jane Smith',
                'cpf' => '98765432100',
                'email' => 'jane.smith@example.com',
                'birthdate' => '1985-05-15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
         
        ]);
    }
}
