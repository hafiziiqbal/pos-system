<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'tenant_id' => null,
            'store_id'  => null,
            'name'      => 'Programmer',
            'email'     => 'programmer@example.com',
            'password'  => Hash::make('password123'),
            'role'      => 'programmer',
            'status'    => 'active',
        ]);
    }
}
