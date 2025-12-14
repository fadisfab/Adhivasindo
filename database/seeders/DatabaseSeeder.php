<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'token' => Str::random(60)
        ]);

        User::create([
            'name' => 'User Test',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'token' => Str::random(60)
        ]);
    }
}