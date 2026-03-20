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
        Schema::create('arisan_draws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arisan_id')->constrained()->cascadeOnDelete();
            $table->string('period_month', 7); // e.g. "2024-06"
            $table->timestamp('drawn_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['arisan_id', 'period_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arisan_draws');
    }
};
