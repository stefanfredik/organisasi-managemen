<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General
            [
                'key' => 'organization_name',
                'value' => 'Sistem Manajemen Organisasi',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Nama organisasi yang ditampilkan di sistem.',
            ],
            [
                'key' => 'organization_short_name',
                'value' => 'SMO',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Nama pendek atau singkatan organisasi.',
            ],
            [
                'key' => 'organization_email',
                'value' => 'admin@organisasi.com',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Email resmi organisasi.',
            ],
            [
                'key' => 'organization_phone',
                'value' => '08123456789',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Nomor telepon resmi organisasi.',
            ],
            [
                'key' => 'organization_address',
                'value' => 'Jl. Merdeka No. 1, Jakarta',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Alamat kantor organisasi.',
            ],
            
            // Financial
            [
                'key' => 'currency_symbol',
                'value' => 'Rp',
                'group' => 'financial',
                'type' => 'string',
                'description' => 'Simbol mata uang (misal: Rp, $, €).',
            ],
            [
                'key' => 'bank_name',
                'value' => 'Bank Central Asia (BCA)',
                'group' => 'financial',
                'type' => 'string',
                'description' => 'Nama bank untuk instruksi pembayaran.',
            ],
            [
                'key' => 'bank_account_number',
                'value' => '1234567890',
                'group' => 'financial',
                'type' => 'string',
                'description' => 'Nomor rekening bank.',
            ],
            [
                'key' => 'bank_account_name',
                'value' => 'Bendahara Organisasi',
                'group' => 'financial',
                'type' => 'string',
                'description' => 'Nama pemilik rekening bank.',
            ],
            
            // Social Media
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/organisasi',
                'group' => 'social',
                'type' => 'string',
                'description' => 'Link Facebook organisasi.',
            ],
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/organisasi',
                'group' => 'social',
                'type' => 'string',
                'description' => 'Link Instagram organisasi.',
            ],
            [
                'key' => 'social_twitter',
                'value' => 'https://twitter.com/organisasi',
                'group' => 'social',
                'type' => 'string',
                'description' => 'Link Twitter/X organisasi.',
            ],
            [
                'key' => 'social_youtube',
                'value' => 'https://youtube.com/@organisasi',
                'group' => 'social',
                'type' => 'string',
                'description' => 'Link Channel YouTube organisasi.',
            ],
            [
                'key' => 'social_whatsapp',
                'value' => '628123456789',
                'group' => 'social',
                'type' => 'string',
                'description' => 'Nomor WhatsApp (gunakan format internasional saja, misal 628...).',
            ],
            
            // SEO & Portal
            [
                'key' => 'portal_meta_description',
                'value' => 'Sistem Manajemen Organisasi - Mengelola data anggota, keuangan, dan kegiatan secara transparan.',
                'group' => 'portal',
                'type' => 'string',
                'description' => 'Deskripsi SEO untuk pencarian Google.',
            ],
            [
                'key' => 'portal_welcome_text',
                'value' => 'Selamat Datang di Portal Resmi Organisasi Kami',
                'group' => 'portal',
                'type' => 'string',
                'description' => 'Teks sambutan di halaman utama portal publik.',
            ],
            
            // System
            [
                'key' => 'allow_public_registration',
                'value' => '0',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Izinkan pendaftaran akun oleh publik.',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan mode pemeliharaan sistem.',
            ],
            [
                'key' => 'enable_donations',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan fitur donasi publik.',
            ],
            [
                'key' => 'enable_gallery',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan fitur galeri foto.',
            ],

            // Access Control (Permissions)
            [
                'key' => 'role_permissions_ketua',
                'value' => json_encode(['view_dashboard', 'manage_members', 'manage_finance', 'manage_events', 'view_reports', 'manage_contributions', 'manage_contribution_types', 'manage_announcements', 'view_announcements', 'view_meeting_minutes', 'manage_organization_structures', 'manage_albums']),
                'group' => 'access_control',
                'type' => 'json',
                'description' => 'Hak akses untuk role Ketua.',
            ],
            [
                'key' => 'role_permissions_bendahara',
                'value' => json_encode(['view_dashboard', 'manage_finance', 'view_reports', 'manage_contributions', 'manage_contribution_types', 'view_announcements', 'view_meeting_minutes', 'view_albums']),
                'group' => 'access_control',
                'type' => 'json',
                'description' => 'Hak akses untuk role Bendahara.',
            ],
            [
                'key' => 'role_permissions_sekretaris',
                'value' => json_encode(['view_dashboard', 'manage_members', 'manage_events', 'view_contributions', 'view_contribution_types', 'manage_announcements', 'view_announcements', 'manage_meeting_minutes', 'manage_organization_structures', 'manage_albums']),
                'group' => 'access_control',
                'type' => 'json',
                'description' => 'Hak akses untuk role Sekretaris.',
            ],
            [
                'key' => 'role_permissions_anggota',
                'value' => json_encode(['view_dashboard', 'view_events', 'view_donations', 'view_contributions', 'view_announcements', 'view_meeting_minutes', 'view_albums']),
                'group' => 'access_control',
                'type' => 'json',
                'description' => 'Hak akses untuk role Anggota.',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
