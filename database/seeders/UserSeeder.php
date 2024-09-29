<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // \App\Models\User::factory(3)->create();

        $user = [
            'email' => 'testuser@example.com',
            'password' => 'testuser'
        ];

        \App\Models\User::factory()->create($user);

        Log::info('New Test User (Seed)', ['data' => $user]);
    }
}