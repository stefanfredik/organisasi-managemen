<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add position column to members
        Schema::table('members', function (Blueprint $table) {
            $table->enum('position', ['ketua', 'bendahara', 'sekretaris', 'anggota'])
                  ->default('anggota')
                  ->after('user_id');
        });

        // 2. Migrate data: set member.position from user.role
        $positionRoles = ['ketua', 'bendahara', 'sekretaris', 'anggota'];

        $users = DB::table('users')
            ->whereIn('role', $positionRoles)
            ->get();

        foreach ($users as $user) {
            $member = DB::table('members')->where('user_id', $user->id)->first();

            if ($member) {
                // Update existing member's position
                DB::table('members')
                    ->where('id', $member->id)
                    ->update(['position' => $user->role]);
            } else {
                // Create minimal member record for users without one
                $lastCode = DB::table('members')
                    ->where('member_code', 'like', 'MBR-' . date('Y') . '-%')
                    ->orderByDesc('member_code')
                    ->value('member_code');

                $nextNum = 1;
                if ($lastCode) {
                    $parts = explode('-', $lastCode);
                    $nextNum = (int) end($parts) + 1;
                }
                $memberCode = 'MBR-' . date('Y') . '-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

                DB::table('members')->insert([
                    'user_id' => $user->id,
                    'position' => $user->role,
                    'member_code' => $memberCode,
                    'full_name' => $user->name,
                    'email' => $user->email,
                    'join_date' => now()->toDateString(),
                    'status' => $user->status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Change all non-admin users to role 'member'
        DB::table('users')
            ->whereIn('role', $positionRoles)
            ->update(['role' => 'member']);

        // 4. Alter users.role enum to only admin/member
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role_new', ['admin', 'member'])->default('member')->after('role');
        });

        DB::table('users')->update([
            'role_new' => DB::raw('role'),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('role_new', 'role');
        });
    }

    public function down(): void
    {
        // 1. Expand users.role back to 5 values
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role_old', ['admin', 'ketua', 'bendahara', 'sekretaris', 'anggota'])
                  ->default('anggota')
                  ->after('role');
        });

        DB::table('users')->update([
            'role_old' => DB::raw('role'),
        ]);

        // 2. Restore users.role from members.position
        $members = DB::table('members')
            ->whereNotNull('user_id')
            ->whereIn('position', ['ketua', 'bendahara', 'sekretaris', 'anggota'])
            ->get();

        foreach ($members as $member) {
            DB::table('users')
                ->where('id', $member->user_id)
                ->where('role_old', 'member')
                ->update(['role_old' => $member->position]);
        }

        // Replace remaining 'member' with 'anggota'
        DB::table('users')
            ->where('role_old', 'member')
            ->update(['role_old' => 'anggota']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('role_old', 'role');
        });

        // 3. Drop position column from members
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
