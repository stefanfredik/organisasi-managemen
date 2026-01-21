<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('meeting_minutes', function (Blueprint $table) {
            $table->id();
            $table->date('meeting_date');
            $table->string('agenda');
            $table->json('participants')->nullable();
            $table->longText('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('meeting_minute_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_minute_id')->constrained('meeting_minutes')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_minute_attachments');
        Schema::dropIfExists('meeting_minutes');
    }
};

