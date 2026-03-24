<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'admin@topupatlas.local');
        $password = (string) env('ADMIN_PASSWORD', 'Admin@12345!');
        $name = (string) env('ADMIN_NAME', 'TopUp Admin');

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'admin',
                'account_status' => 'ACTIVE',
            ],
        );
    }
}
