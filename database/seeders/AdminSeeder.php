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
        // Remove old test admin accounts
        User::where('email', 'admin@example.test')
            ->orWhere('email', 'admin@admin.com')
            ->delete();

        User::updateOrCreate(
            ['email' => 'argolistppob@admin.com'],
            [
                'name' => 'Super Admin',
                'username' => 'ArgolistPPOBAdmin',
                'email' => 'argolistppob@admin.com',
                'password' => Hash::make('4rg0L15T$_@'),
                'is_admin' => true,
                'is_verified' => true,
            ]
        );
    }
}
