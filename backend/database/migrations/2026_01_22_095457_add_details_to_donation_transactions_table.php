<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('donation_transactions', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('donation_id')->constrained('members')->nullOnDelete();
            $table->string('receipt_path')->nullable()->after('notes');
            $table->enum('status', ['pending', 'paid', 'rejected'])->default('paid')->after('receipt_path');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donation_transactions', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->dropColumn(['member_id', 'receipt_path', 'status', 'verified_by', 'verified_at']);
        });
    }
};
