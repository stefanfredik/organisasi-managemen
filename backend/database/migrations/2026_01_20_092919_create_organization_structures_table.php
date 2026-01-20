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
        Schema::create('organization_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('position_name');
            $table->integer('level')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('organization_structures')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->integer('period_start');
            $table->integer('period_end')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_structures');
    }
};
