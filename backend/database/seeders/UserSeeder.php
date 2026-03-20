<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin user (no member record needed)
        User::firstOrCreate(
            ['email' => 'admin@organisasi.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Create members with different positions
        $members = [
            ['name' => 'Ketua Organisasi', 'email' => 'ketua@organisasi.test', 'position' => 'ketua'],
            ['name' => 'Bendahara Organisasi', 'email' => 'bendahara@organisasi.test', 'position' => 'bendahara'],
            ['name' => 'Sekretaris Organisasi', 'email' => 'sekretaris@organisasi.test', 'position' => 'sekretaris'],
            ['name' => 'Anggota Organisasi', 'email' => 'anggota@organisasi.test', 'position' => 'anggota'],
        ];

        foreach ($members as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => 'member',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            Member::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'position' => $data['position'],
                    'full_name' => $data['name'],
                    'email' => $data['email'],
                    'member_code' => Member::generateMemberCode(),
                    'join_date' => now()->toDateString(),
                    'status' => 'active',
                ]
            );
        }

        $this->command->info('Users seeded successfully!');
    }
}
