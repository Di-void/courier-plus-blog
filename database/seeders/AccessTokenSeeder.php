<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccessTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $token = env('AUTH_TOKEN', 'CourierPlus@321');

        $ctx = [
            'tokenable_type' => 'App\Models\User',
            'tokenable_id' => 1,
            'name' => 'testtoken',
            'token' => hash('sha256', $token),
            'abilities' => "['*']",
        ];

        DB::table('personal_access_tokens')->insert($ctx);

        Log::info('New Test Login (Seed)', ['data' => $ctx]);
    }
}