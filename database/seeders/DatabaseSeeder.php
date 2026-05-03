<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@majalis.sn'],
            [
                'name' => 'Admin Majalis',
                'password' => Hash::make('Majalis2003@'),
                'role' => 'admin',
                'phone' => '+221-77-840-19-04',
            ]
        );
    }
}