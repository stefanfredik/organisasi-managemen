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
        // Check if the table exists and has the participants column
        if (Schema::hasTable('meeting_minutes') && Schema::hasColumn('meeting_minutes', 'participants')) {
            // Get all existing records
            $minutes = DB::table('meeting_minutes')->get();
            
            // Change column type to JSON
            Schema::table('meeting_minutes', function (Blueprint $table) {
                $table->json('participants')->nullable()->change();
            });
            
            // Update existing records to ensure they have valid JSON
            foreach ($minutes as $minute) {
                if ($minute->participants && !is_null($minute->participants)) {
                    // If it's already JSON, decode and re-encode to ensure validity
                    $participants = json_decode($minute->participants, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // If not valid JSON, set to empty array
                        DB::table('meeting_minutes')
                            ->where('id', $minute->id)
                            ->update(['participants' => json_encode([])]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('meeting_minutes') && Schema::hasColumn('meeting_minutes', 'participants')) {
            Schema::table('meeting_minutes', function (Blueprint $table) {
                $table->text('participants')->nullable()->change();
            });
        }
    }
};
