<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('nik')->nullable()->unique()->after('member_code');
            $table->string('nickname')->nullable()->after('full_name');
            $table->enum('gender', ['male', 'female'])->nullable()->after('nickname');
            $table->string('religion')->nullable()->after('gender');
            $table->string('origin_hamlet')->nullable()->after('religion');
            $table->string('origin_village')->nullable()->after('origin_hamlet');
            $table->string('origin_subdistrict')->nullable()->after('origin_village');
            $table->string('origin_regency')->nullable()->after('origin_subdistrict');
            $table->string('origin_province')->nullable()->after('origin_regency');
            $table->text('domicile_address')->nullable()->after('address');
            $table->enum('living_status', ['kos', 'rumah'])->nullable()->after('domicile_address');
            $table->string('marital_status')->nullable()->after('living_status');
            $table->string('occupation')->nullable()->after('marital_status');
            $table->date('arrival_date_bali')->nullable()->after('occupation');
            $table->boolean('bpjs_health_active')->default(false)->after('arrival_date_bali');
            $table->boolean('bpjs_employment_active')->default(false)->after('bpjs_health_active');
            $table->string('ktp_photo')->nullable()->after('photo');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'nik',
                'nickname',
                'gender',
                'religion',
                'origin_hamlet',
                'origin_village',
                'origin_subdistrict',
                'origin_regency',
                'origin_province',
                'domicile_address',
                'living_status',
                'marital_status',
                'occupation',
                'arrival_date_bali',
                'bpjs_health_active',
                'bpjs_employment_active',
                'ktp_photo',
            ]);
        });
    }
};

