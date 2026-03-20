<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Seed initial positions to the new positions table
        $defaultPositions = [
            ['code' => 'ketua', 'name' => 'Ketua', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'bendahara', 'name' => 'Bendahara', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'sekretaris', 'name' => 'Sekretaris', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'anggota', 'name' => 'Anggota', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('positions')->insert($defaultPositions);

        // 2. Add position_id to members table
        Schema::table('members', function (Blueprint $table) {
            $table->foreignId('position_id')->nullable()->after('position')
                  ->constrained('positions')->nullOnDelete();
        });

        // 3. Migrate existing member data to position_id
        $positions = DB::table('positions')->get()->keyBy('code');

        DB::table('members')->orderBy('id')->chunk(100, function ($members) use ($positions) {
            foreach ($members as $member) {
                // Determine the correct position_id based on the old string 'position' enum
                $code = $member->position ?? 'anggota';
                $positionId = $positions->get($code)->id ?? $positions->get('anggota')->id;

                DB::table('members')
                    ->where('id', $member->id)
                    ->update(['position_id' => $positionId]);
            }
        });

        // 4. Drop the old position column
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Re-add position enum column
        Schema::table('members', function (Blueprint $table) {
            $table->enum('position', ['ketua', 'bendahara', 'sekretaris', 'anggota'])
                  ->default('anggota')
                  ->after('position_id');
        });

        // 2. Migrate data back
        $positions = DB::table('positions')->get()->keyBy('id');

        DB::table('members')->orderBy('id')->chunk(100, function ($members) use ($positions) {
            foreach ($members as $member) {
                $pos = $positions->get($member->position_id);
                $code = $pos ? $pos->code : 'anggota';
                
                DB::table('members')
                    ->where('id', $member->id)
                    ->update(['position' => $code]);
            }
        });

        // 3. Drop position_id column
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
        });

        // 4. Truncate positions table
        DB::table('positions')->truncate();
    }
};
