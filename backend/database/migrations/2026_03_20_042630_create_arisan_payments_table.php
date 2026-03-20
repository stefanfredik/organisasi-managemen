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
        Schema::create('arisan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arisan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('month', 7); // e.g. "2024-05"
            $table->decimal('amount', 15, 2);
            $table->timestamp('payment_date')->useCurrent();
            $table->timestamps();
            
            $table->unique(['arisan_id', 'member_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arisan_payments');
    }
};
