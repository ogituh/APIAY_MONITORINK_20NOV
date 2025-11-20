<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'bpid'     => 'KYBI1',
            'username' => 'kayaba',
            'password' => Hash::make('kayaba123'),
            'phone'    => '08155667788',
            'status'   => 1,
        ]);
    }
}

