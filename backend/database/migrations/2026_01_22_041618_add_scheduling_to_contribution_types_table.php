<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contribution_types', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('due_date');
            $table->date('end_date')->nullable()->after('start_date');
            $table->integer('recurring_day')->nullable()->after('end_date')->comment('Day of week (1-7) or Day of month (1-31)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contribution_types', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'recurring_day']);
        });
    }
};
