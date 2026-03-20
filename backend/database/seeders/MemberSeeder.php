<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $user = User::factory()->create([
                'role' => 'member',
                'status' => 'active',
                'password' => Hash::make('password'),
            ]);

            Member::factory()
                ->active()
                ->create([
                    'user_id' => $user->id,
                    'position_id' => \App\Models\Position::where('code', 'anggota')->value('id') ?? 4,
                    'member_code' => Member::generateMemberCode(),
                    'full_name' => $user->name,
                    'email' => $user->email,
                ]);
        }
    }
}
