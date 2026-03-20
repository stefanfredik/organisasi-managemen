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
        Schema::create('arisan_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arisan_draw_id')->constrained()->cascadeOnDelete();
            $table->foreignId('arisan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Seorang member tidak boleh menang lebih dari satu kali di satu arisan (kapanpun draw-nya)
            $table->unique(['arisan_id', 'member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arisan_winners');
    }
};
