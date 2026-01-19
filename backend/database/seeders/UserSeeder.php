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
        // Create Admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@organisasi.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create Ketua (Chairman)
        User::create([
            'name' => 'Ketua Organisasi',
            'email' => 'ketua@organisasi.test',
            'password' => Hash::make('password'),
            'role' => 'ketua',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create Bendahara (Treasurer)
        User::create([
            'name' => 'Bendahara Organisasi',
            'email' => 'bendahara@organisasi.test',
            'password' => Hash::make('password'),
            'role' => 'bendahara',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create Sekretaris (Secretary)
        User::create([
            'name' => 'Sekretaris Organisasi',
            'email' => 'sekretaris@organisasi.test',
            'password' => Hash::make('password'),
            'role' => 'sekretaris',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create Anggota (Member)
        User::create([
            'name' => 'Anggota Organisasi',
            'email' => 'anggota@organisasi.test',
            'password' => Hash::make('password'),
            'role' => 'anggota',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Users seeded successfully!');
    }
}
