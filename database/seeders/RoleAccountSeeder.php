<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAccountSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'Credira123!';

        $accounts = [
            [
                'name' => 'Credira Admin',
                'email' => 'admin@credira.test',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Credira Marketing',
                'email' => 'marketing@credira.test',
                'role' => User::ROLE_MARKETING,
            ],
            [
                'name' => 'Credira CEO',
                'email' => 'ceo@credira.test',
                'role' => User::ROLE_CEO,
            ],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'role' => $account['role'],
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
