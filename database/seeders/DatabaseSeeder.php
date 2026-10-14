<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@banyuwangi.test',
            'role' => 'tenant_admin',
        ]);

        $demo = [
            ['name' => 'Super Admin Wisata', 'email' => 'admin@banyuwangi.test', 'role' => 'super_admin'],
            ['name' => 'Pengelola Destinasi', 'email' => 'pengelola@banyuwangi.test', 'role' => 'tenant_admin'],
        ];

        foreach ($demo as $row) {
            User::firstOrNew(['email' => $row['email']])
                ->forceFill([
                    'name' => $row['name'],
                    'role' => $row['role'],
                    'status' => 'active',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ])
                ->save();
        }
    }
}