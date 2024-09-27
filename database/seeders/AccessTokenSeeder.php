<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $token = env('AUTH_TOKEN', 'CourierPlus@321');

        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => 'App\Models\User',
            'tokenable_id' => 1,
            'name' => 'testtoken',
            'token' => hash('sha256', $token),
            'abilities' => "['*']",
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}