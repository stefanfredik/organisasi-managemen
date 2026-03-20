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
            // ============================================
            // GENERAL — Informasi Dasar Organisasi
            // ============================================
            [
                'key' => 'organization_name',
                'value' => 'Sistem Manajemen Organisasi',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Nama lengkap organisasi yang ditampilkan di sistem.',
            ],
            [
                'key' => 'organization_short_name',
                'value' => 'SMO',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Nama pendek atau singkatan organisasi.',
            ],
            [
                'key' => 'organization_logo',
                'value' => null,
                'group' => 'general',
                'type' => 'image',
                'description' => 'Logo organisasi (rekomendasi: PNG transparan, maks 2MB).',
            ],
            [
                'key' => 'organization_favicon',
                'value' => null,
                'group' => 'general',
                'type' => 'image',
                'description' => 'Favicon untuk browser tab (rekomendasi: 32x32px atau 64x64px, PNG/ICO).',
            ],
            [
                'key' => 'organization_type',
                'value' => 'Organisasi Masyarakat',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Jenis organisasi (contoh: RT/RW, Karang Taruna, Yayasan, Komunitas).',
            ],
            [
                'key' => 'organization_founded_date',
                'value' => null,
                'group' => 'general',
                'type' => 'string',
                'description' => 'Tanggal berdiri organisasi (format: YYYY-MM-DD).',
            ],
            [
                'key' => 'organization_registration_number',
                'value' => null,
                'group' => 'general',
                'type' => 'string',
                'description' => 'Nomor registrasi/SK resmi organisasi (jika ada).',
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
                'description' => 'Alamat kantor/sekretariat organisasi.',
            ],
            [
                'key' => 'organization_city',
                'value' => 'Jakarta',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Kota/Kabupaten organisasi.',
            ],
            [
                'key' => 'organization_province',
                'value' => 'DKI Jakarta',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Provinsi organisasi.',
            ],
            [
                'key' => 'organization_postal_code',
                'value' => null,
                'group' => 'general',
                'type' => 'string',
                'description' => 'Kode pos.',
            ],
            [
                'key' => 'organization_description',
                'value' => null,
                'group' => 'general',
                'type' => 'text',
                'description' => 'Deskripsi singkat tentang organisasi (ditampilkan di portal & dokumen).',
            ],
            [
                'key' => 'organization_motto',
                'value' => null,
                'group' => 'general',
                'type' => 'string',
                'description' => 'Motto atau slogan organisasi.',
            ],
            [
                'key' => 'organization_npwp',
                'value' => null,
                'group' => 'general',
                'type' => 'string',
                'description' => 'Nomor NPWP organisasi (jika ada).',
            ],

            // ============================================
            // FINANCIAL — Pengaturan Keuangan & Bank
            // ============================================
            [
                'key' => 'currency_symbol',
                'value' => 'Rp',
                'group' => 'financial',
                'type' => 'string',
                'description' => 'Simbol mata uang (misal: Rp, $, €).',
            ],
            [
                'key' => 'currency_code',
                'value' => 'IDR',
                'group' => 'financial',
                'type' => 'string',
                'description' => 'Kode mata uang ISO 4217 (misal: IDR, USD, EUR).',
            ],
            [
                'key' => 'bank_name',
                'value' => 'Bank Central Asia (BCA)',
                'group' => 'financial',
                'type' => 'string',
                'description' => 'Nama bank utama untuk penerimaan pembayaran.',
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
            [
                'key' => 'bank_branch',
                'value' => null,
                'group' => 'financial',
                'type' => 'string',
                'description' => 'Cabang bank (opsional).',
            ],
            [
                'key' => 'ewallet_provider',
                'value' => null,
                'group' => 'financial',
                'type' => 'string',
                'description' => 'Nama penyedia e-wallet (misal: GoPay, OVO, Dana, ShopeePay).',
            ],
            [
                'key' => 'ewallet_number',
                'value' => null,
                'group' => 'financial',
                'type' => 'string',
                'description' => 'Nomor e-wallet untuk penerimaan pembayaran.',
            ],
            [
                'key' => 'ewallet_name',
                'value' => null,
                'group' => 'financial',
                'type' => 'string',
                'description' => 'Nama pemilik akun e-wallet.',
            ],
            [
                'key' => 'qris_image',
                'value' => null,
                'group' => 'financial',
                'type' => 'image',
                'description' => 'Gambar kode QRIS untuk pembayaran (maks 2MB).',
            ],
            [
                'key' => 'payment_instructions',
                'value' => 'Silakan transfer ke rekening di atas dan konfirmasi pembayaran melalui sistem.',
                'group' => 'financial',
                'type' => 'text',
                'description' => 'Instruksi pembayaran yang ditampilkan kepada anggota.',
            ],
            [
                'key' => 'fiscal_year_start_month',
                'value' => '1',
                'group' => 'financial',
                'type' => 'integer',
                'description' => 'Bulan awal tahun buku/fiskal (1 = Januari, 4 = April, dst).',
            ],
            [
                'key' => 'payment_deadline_days',
                'value' => '30',
                'group' => 'financial',
                'type' => 'integer',
                'description' => 'Batas waktu pembayaran iuran dalam hari setelah tagihan dibuat.',
            ],
            [
                'key' => 'late_payment_penalty',
                'value' => '0',
                'group' => 'financial',
                'type' => 'boolean',
                'description' => 'Aktifkan denda keterlambatan pembayaran iuran.',
            ],
            [
                'key' => 'late_payment_penalty_amount',
                'value' => '0',
                'group' => 'financial',
                'type' => 'integer',
                'description' => 'Nominal denda keterlambatan per hari (dalam mata uang yang digunakan).',
            ],

            // ============================================
            // SOCIAL — Media Sosial
            // ============================================
            [
                'key' => 'social_facebook',
                'value' => null,
                'group' => 'social',
                'type' => 'string',
                'description' => 'Link halaman Facebook organisasi.',
            ],
            [
                'key' => 'social_instagram',
                'value' => null,
                'group' => 'social',
                'type' => 'string',
                'description' => 'Link profil Instagram organisasi.',
            ],
            [
                'key' => 'social_twitter',
                'value' => null,
                'group' => 'social',
                'type' => 'string',
                'description' => 'Link profil Twitter/X organisasi.',
            ],
            [
                'key' => 'social_youtube',
                'value' => null,
                'group' => 'social',
                'type' => 'string',
                'description' => 'Link channel YouTube organisasi.',
            ],
            [
                'key' => 'social_tiktok',
                'value' => null,
                'group' => 'social',
                'type' => 'string',
                'description' => 'Link profil TikTok organisasi.',
            ],
            [
                'key' => 'social_whatsapp',
                'value' => null,
                'group' => 'social',
                'type' => 'string',
                'description' => 'Nomor WhatsApp (format internasional, misal: 628123456789).',
            ],
            [
                'key' => 'social_telegram',
                'value' => null,
                'group' => 'social',
                'type' => 'string',
                'description' => 'Link grup/channel Telegram organisasi.',
            ],
            [
                'key' => 'social_website',
                'value' => null,
                'group' => 'social',
                'type' => 'string',
                'description' => 'Website resmi organisasi (jika berbeda dari portal ini).',
            ],

            // ============================================
            // PORTAL — Pengaturan Portal Publik
            // ============================================
            [
                'key' => 'portal_meta_title',
                'value' => null,
                'group' => 'portal',
                'type' => 'string',
                'description' => 'Judul halaman untuk SEO (muncul di tab browser & Google).',
            ],
            [
                'key' => 'portal_meta_description',
                'value' => 'Sistem Manajemen Organisasi - Mengelola data anggota, keuangan, dan kegiatan secara transparan.',
                'group' => 'portal',
                'type' => 'text',
                'description' => 'Deskripsi meta untuk SEO (muncul di hasil pencarian Google).',
            ],
            [
                'key' => 'portal_meta_keywords',
                'value' => 'organisasi, manajemen, anggota, keuangan, kegiatan',
                'group' => 'portal',
                'type' => 'string',
                'description' => 'Kata kunci SEO dipisahkan koma.',
            ],
            [
                'key' => 'portal_welcome_text',
                'value' => 'Selamat Datang di Portal Resmi Organisasi Kami',
                'group' => 'portal',
                'type' => 'string',
                'description' => 'Teks sambutan utama di halaman depan portal publik.',
            ],
            [
                'key' => 'portal_welcome_subtitle',
                'value' => 'Bersama membangun komunitas yang lebih baik melalui transparansi dan kolaborasi.',
                'group' => 'portal',
                'type' => 'text',
                'description' => 'Sub-judul di bawah teks sambutan halaman depan.',
            ],
            [
                'key' => 'portal_about_text',
                'value' => null,
                'group' => 'portal',
                'type' => 'text',
                'description' => 'Deskripsi singkat organisasi yang ditampilkan di halaman Tentang Kami.',
            ],
            [
                'key' => 'portal_footer_text',
                'value' => null,
                'group' => 'portal',
                'type' => 'text',
                'description' => 'Teks tambahan yang ditampilkan di footer portal.',
            ],
            [
                'key' => 'portal_google_maps_embed',
                'value' => null,
                'group' => 'portal',
                'type' => 'text',
                'description' => 'Kode embed Google Maps (salin dari Google Maps > Share > Embed).',
            ],
            [
                'key' => 'portal_hero_image',
                'value' => null,
                'group' => 'portal',
                'type' => 'image',
                'description' => 'Gambar hero/banner utama halaman depan portal publik (rekomendasi: 1920x1080px, maks 2MB).',
            ],
            [
                'key' => 'portal_show_member_count',
                'value' => '1',
                'group' => 'portal',
                'type' => 'boolean',
                'description' => 'Tampilkan jumlah anggota di halaman portal publik.',
            ],
            [
                'key' => 'portal_show_event_section',
                'value' => '1',
                'group' => 'portal',
                'type' => 'boolean',
                'description' => 'Tampilkan section kegiatan di halaman depan portal.',
            ],
            [
                'key' => 'portal_show_donation_section',
                'value' => '1',
                'group' => 'portal',
                'type' => 'boolean',
                'description' => 'Tampilkan section donasi/dukungan di halaman depan portal.',
            ],
            [
                'key' => 'portal_show_gallery_section',
                'value' => '1',
                'group' => 'portal',
                'type' => 'boolean',
                'description' => 'Tampilkan section galeri foto di halaman depan portal.',
            ],
            [
                'key' => 'portal_contact_email',
                'value' => null,
                'group' => 'portal',
                'type' => 'string',
                'description' => 'Email kontak yang ditampilkan di halaman portal (jika berbeda dari email organisasi).',
            ],
            [
                'key' => 'portal_primary_color',
                'value' => null,
                'group' => 'portal',
                'type' => 'string',
                'description' => 'Warna utama tema portal publik (format hex, misal: #3B82F6).',
            ],
            [
                'key' => 'app_theme_color',
                'value' => 'indigo',
                'group' => 'system',
                'type' => 'string',
                'description' => 'Tema warna utama aplikasi.',
            ],

            // ============================================
            // SYSTEM — Konfigurasi Sistem
            // ============================================
            [
                'key' => 'allow_public_registration',
                'value' => '0',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Izinkan pendaftaran akun baru oleh publik.',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan mode pemeliharaan (semua halaman menampilkan pesan maintenance).',
            ],
            [
                'key' => 'enable_donations',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan modul donasi publik.',
            ],
            [
                'key' => 'enable_gallery',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan modul galeri/album foto.',
            ],
            [
                'key' => 'enable_contributions',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan modul iuran anggota.',
            ],
            [
                'key' => 'enable_events',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan modul kegiatan/event.',
            ],
            [
                'key' => 'enable_meeting_minutes',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan modul notulensi rapat.',
            ],
            [
                'key' => 'enable_announcements',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan modul pengumuman.',
            ],
            [
                'key' => 'enable_public_portal',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan halaman portal publik (website organisasi).',
            ],
            [
                'key' => 'default_member_status',
                'value' => 'active',
                'group' => 'system',
                'type' => 'string',
                'description' => 'Status default anggota baru saat didaftarkan (active/inactive).',
            ],
            [
                'key' => 'pagination_per_page',
                'value' => '15',
                'group' => 'system',
                'type' => 'integer',
                'description' => 'Jumlah data per halaman pada tabel (10, 15, 25, 50).',
            ],
            [
                'key' => 'app_timezone',
                'value' => 'Asia/Jakarta',
                'group' => 'system',
                'type' => 'string',
                'description' => 'Zona waktu sistem (misal: Asia/Jakarta, Asia/Makassar, Asia/Jayapura).',
            ],
            [
                'key' => 'app_locale',
                'value' => 'id',
                'group' => 'system',
                'type' => 'string',
                'description' => 'Bahasa sistem (id = Indonesia, en = English).',
            ],
            [
                'key' => 'enable_email_notifications',
                'value' => '0',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan notifikasi via email (pengumuman, tagihan, dll).',
            ],
            [
                'key' => 'enable_whatsapp_notifications',
                'value' => '0',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan notifikasi via WhatsApp (memerlukan integrasi API).',
            ],
            [
                'key' => 'enable_member_self_update',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Izinkan anggota memperbarui data profil sendiri.',
            ],
            [
                'key' => 'enable_member_card',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan fitur cetak kartu anggota digital.',
            ],
            [
                'key' => 'enable_financial_reports',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan fitur laporan keuangan otomatis.',
            ],
            [
                'key' => 'max_upload_size',
                'value' => '2048',
                'group' => 'system',
                'type' => 'integer',
                'description' => 'Ukuran maksimal file upload dalam KB (default: 2048 = 2MB).',
            ],
            [
                'key' => 'session_lifetime',
                'value' => '120',
                'group' => 'system',
                'type' => 'integer',
                'description' => 'Durasi sesi login dalam menit sebelum otomatis logout.',
            ],
            [
                'key' => 'date_format',
                'value' => 'd/m/Y',
                'group' => 'system',
                'type' => 'string',
                'description' => 'Format tampilan tanggal (d/m/Y, Y-m-d, d M Y, dll).',
            ],
            [
                'key' => 'backup_auto_enabled',
                'value' => '0',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan backup database otomatis secara berkala.',
            ],
            [
                'key' => 'backup_frequency_days',
                'value' => '7',
                'group' => 'system',
                'type' => 'integer',
                'description' => 'Frekuensi backup otomatis dalam hari.',
            ],

            // ============================================
            // ACCESS CONTROL — Hak Akses Per Role
            // ============================================
            [
                'key' => 'role_permissions_ketua',
                'value' => json_encode(['view_dashboard', 'manage_members', 'manage_finance', 'manage_events', 'view_reports', 'manage_contributions', 'manage_contribution_types', 'manage_announcements', 'view_announcements', 'view_meeting_minutes', 'manage_vision_missions', 'manage_organization_structures', 'manage_albums', 'view_donations', 'manage_arisans', 'view_arisans']),
                'group' => 'access_control',
                'type' => 'json',
                'description' => 'Hak akses untuk role Ketua.',
            ],
            [
                'key' => 'role_permissions_bendahara',
                'value' => json_encode(['view_dashboard', 'manage_finance', 'view_reports', 'manage_contributions', 'manage_contribution_types', 'view_announcements', 'view_meeting_minutes', 'view_vision_missions', 'view_albums', 'view_donations', 'view_members', 'manage_arisans', 'view_arisans']),
                'group' => 'access_control',
                'type' => 'json',
                'description' => 'Hak akses untuk role Bendahara.',
            ],
            [
                'key' => 'role_permissions_sekretaris',
                'value' => json_encode(['view_dashboard', 'manage_members', 'manage_events', 'view_contributions', 'view_contribution_types', 'manage_announcements', 'view_announcements', 'manage_meeting_minutes', 'view_vision_missions', 'manage_organization_structures', 'manage_albums', 'view_donations', 'view_reports', 'view_arisans']),
                'group' => 'access_control',
                'type' => 'json',
                'description' => 'Hak akses untuk role Sekretaris.',
            ],
            [
                'key' => 'role_permissions_anggota',
                'value' => json_encode(['view_dashboard', 'view_events', 'view_donations', 'view_contributions', 'view_announcements', 'view_meeting_minutes', 'view_vision_missions', 'view_albums', 'view_arisans']),
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
