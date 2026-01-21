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
        Schema::table('members', function (Blueprint $table) {
            if (!Schema::hasColumn('members', 'nik')) {
                $table->string('nik', 32)->nullable()->unique()->after('member_code');
            }
            if (!Schema::hasColumn('members', 'nickname')) {
                $table->string('nickname')->nullable()->after('full_name');
            }
            if (!Schema::hasColumn('members', 'gender')) {
                $table->string('gender', 10)->nullable()->after('nickname');
            }
            if (!Schema::hasColumn('members', 'religion')) {
                $table->string('religion', 100)->nullable()->after('gender');
            }
            if (Schema::hasColumn('members', 'email')) {
                $table->string('email')->nullable()->change();
            } else {
                $table->string('email')->nullable();
            }
            if (!Schema::hasColumn('members', 'domicile_address')) {
                $table->text('domicile_address')->nullable()->after('address');
            }
            if (!Schema::hasColumn('members', 'living_status')) {
                $table->enum('living_status', ['kos', 'rumah'])->nullable()->after('domicile_address');
            }
            if (!Schema::hasColumn('members', 'marital_status')) {
                $table->string('marital_status', 50)->nullable()->after('living_status');
            }
            if (!Schema::hasColumn('members', 'occupation')) {
                $table->string('occupation', 100)->nullable()->after('marital_status');
            }
            if (!Schema::hasColumn('members', 'arrival_date_bali')) {
                $table->date('arrival_date_bali')->nullable()->after('occupation');
            }
            if (!Schema::hasColumn('members', 'origin_hamlet')) {
                $table->string('origin_hamlet', 100)->nullable()->after('arrival_date_bali');
            }
            if (!Schema::hasColumn('members', 'origin_village')) {
                $table->string('origin_village', 100)->nullable()->after('origin_hamlet');
            }
            if (!Schema::hasColumn('members', 'origin_subdistrict')) {
                $table->string('origin_subdistrict', 100)->nullable()->after('origin_village');
            }
            if (!Schema::hasColumn('members', 'origin_regency')) {
                $table->string('origin_regency', 100)->nullable()->after('origin_subdistrict');
            }
            if (!Schema::hasColumn('members', 'origin_province')) {
                $table->string('origin_province', 100)->nullable()->after('origin_regency');
            }
            if (!Schema::hasColumn('members', 'bpjs_health_active')) {
                $table->boolean('bpjs_health_active')->default(false)->after('photo');
            }
            if (!Schema::hasColumn('members', 'bpjs_employment_active')) {
                $table->boolean('bpjs_employment_active')->default(false)->after('bpjs_health_active');
            }
            if (!Schema::hasColumn('members', 'ktp_photo')) {
                $table->string('ktp_photo')->nullable()->after('photo');
            }
            // Ensure notes exists (already defined); keep as is
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Drop added columns on rollback
            if (Schema::hasColumn('members', 'nik')) {
                $table->dropUnique(['nik']);
                $table->dropColumn('nik');
            }
            foreach ([
                'nickname',
                'gender',
                'religion',
                'domicile_address',
                'living_status',
                'marital_status',
                'occupation',
                'arrival_date_bali',
                'origin_hamlet',
                'origin_village',
                'origin_subdistrict',
                'origin_regency',
                'origin_province',
                'bpjs_health_active',
                'bpjs_employment_active',
                'ktp_photo',
            ] as $col) {
                if (Schema::hasColumn('members', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
