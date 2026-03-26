<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = (string) env('ADMIN_SEED_EMAIL', '');
        $password = (string) env('ADMIN_SEED_PASSWORD', '');

        // Prevent accidental weak default admin account on production deployments.
        if (app()->environment('production') && ($email === '' || $password === '')) {
            return;
        }

        if ($email === '' || $password === '') {
            $email = 'admin@example.test';
            $password = 'ChangeMe123!';
        }

        User::create([
            'name' => 'Admin PPOB',
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);
    }
}
