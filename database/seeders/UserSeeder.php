<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // \App\Models\User::factory(3)->create();

        \App\Models\User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => 'testuser'
        ]);
    }
}