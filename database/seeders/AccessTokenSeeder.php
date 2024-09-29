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
            'created_at' => now(),
            'updated_at' => now()
        ];

        DB::table('personal_access_tokens')->insert($ctx);

        Log::info('New Login: {ctx}', ['ctx' => $ctx]);
    }
}
