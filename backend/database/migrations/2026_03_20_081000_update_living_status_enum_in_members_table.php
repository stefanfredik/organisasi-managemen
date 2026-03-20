<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Using raw SQL for enum update because change() can be problematic with some DB drivers
        DB::statement("ALTER TABLE members MODIFY COLUMN living_status ENUM('kos', 'rumah', 'mess') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE members MODIFY COLUMN living_status ENUM('kos', 'rumah') NULL");
    }
};