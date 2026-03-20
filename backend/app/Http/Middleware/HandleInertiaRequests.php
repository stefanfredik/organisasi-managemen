<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'position' => $request->user()->member?->position?->code,
                    'status' => $request->user()->status,
                    'permissions' => $request->user()->role === 'admin'
                        ? []
                        : \App\Models\Setting::getValue("role_permissions_" . ($request->user()->member?->position?->code ?? ''), []),
                    'member' => $request->user()->member ? [
                        'id' => $request->user()->member->id,
                        'full_name' => $request->user()->member->full_name,
                        'member_code' => $request->user()->member->member_code,
                        'position' => $request->user()->member->position?->code,
                    ] : null,
                ] : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'info' => $request->session()->get('info'),
                'warning' => $request->session()->get('warning'),
            ],
            'appSettings' => [
                'name' => \App\Models\Setting::getValue('organization_name', config('app.name')),
                'short_name' => \App\Models\Setting::getValue('organization_short_name', 'SMO'),
                'logo' => \App\Models\Setting::getValue('organization_logo'),
                'favicon' => \App\Models\Setting::getValue('organization_favicon'),
                'type' => \App\Models\Setting::getValue('organization_type'),
                'founded_date' => \App\Models\Setting::getValue('organization_founded_date'),
                'registration_number' => \App\Models\Setting::getValue('organization_registration_number'),
                'email' => \App\Models\Setting::getValue('organization_email'),
                'phone' => \App\Models\Setting::getValue('organization_phone'),
                'address' => \App\Models\Setting::getValue('organization_address'),
                'city' => \App\Models\Setting::getValue('organization_city'),
                'province' => \App\Models\Setting::getValue('organization_province'),
                'postal_code' => \App\Models\Setting::getValue('organization_postal_code'),
                'description' => \App\Models\Setting::getValue('organization_description'),
                'motto' => \App\Models\Setting::getValue('organization_motto'),
                'npwp' => \App\Models\Setting::getValue('organization_npwp'),
                'portal' => [
                    'meta_title' => \App\Models\Setting::getValue('portal_meta_title'),
                    'meta_description' => \App\Models\Setting::getValue('portal_meta_description'),
                    'meta_keywords' => \App\Models\Setting::getValue('portal_meta_keywords'),
                    'welcome_text' => \App\Models\Setting::getValue('portal_welcome_text', 'Selamat Datang di Portal Resmi Organisasi Kami'),
                    'welcome_subtitle' => \App\Models\Setting::getValue('portal_welcome_subtitle'),
                    'about_text' => \App\Models\Setting::getValue('portal_about_text'),
                    'footer_text' => \App\Models\Setting::getValue('portal_footer_text'),
                    'google_maps_embed' => \App\Models\Setting::getValue('portal_google_maps_embed'),
                    'hero_image' => \App\Models\Setting::getValue('portal_hero_image'),
                    'show_member_count' => \App\Models\Setting::getValue('portal_show_member_count', true),
                    'show_event_section' => \App\Models\Setting::getValue('portal_show_event_section', true),
                    'show_donation_section' => \App\Models\Setting::getValue('portal_show_donation_section', true),
                    'show_gallery_section' => \App\Models\Setting::getValue('portal_show_gallery_section', true),
                    'contact_email' => \App\Models\Setting::getValue('portal_contact_email'),
                    'primary_color' => \App\Models\Setting::getValue('portal_primary_color'),
                ],
                'social' => [
                    'facebook' => \App\Models\Setting::getValue('social_facebook'),
                    'instagram' => \App\Models\Setting::getValue('social_instagram'),
                    'twitter' => \App\Models\Setting::getValue('social_twitter'),
                    'youtube' => \App\Models\Setting::getValue('social_youtube'),
                    'tiktok' => \App\Models\Setting::getValue('social_tiktok'),
                    'whatsapp' => \App\Models\Setting::getValue('social_whatsapp'),
                    'telegram' => \App\Models\Setting::getValue('social_telegram'),
                    'website' => \App\Models\Setting::getValue('social_website'),
                ],
                'features' => [
                    'donations' => \App\Models\Setting::getValue('enable_donations', true),
                    'gallery' => \App\Models\Setting::getValue('enable_gallery', true),
                    'contributions' => \App\Models\Setting::getValue('enable_contributions', true),
                    'events' => \App\Models\Setting::getValue('enable_events', true),
                    'meeting_minutes' => \App\Models\Setting::getValue('enable_meeting_minutes', true),
                    'announcements' => \App\Models\Setting::getValue('enable_announcements', true),
                    'public_portal' => \App\Models\Setting::getValue('enable_public_portal', true),
                    'email_notifications' => \App\Models\Setting::getValue('enable_email_notifications', false),
                    'whatsapp_notifications' => \App\Models\Setting::getValue('enable_whatsapp_notifications', false),
                    'member_self_update' => \App\Models\Setting::getValue('enable_member_self_update', true),
                    'member_card' => \App\Models\Setting::getValue('enable_member_card', true),
                    'financial_reports' => \App\Models\Setting::getValue('enable_financial_reports', true),
                ],
                'financial' => [
                    'currency' => \App\Models\Setting::getValue('currency_symbol', 'Rp'),
                    'currency_code' => \App\Models\Setting::getValue('currency_code', 'IDR'),
                    'bank_name' => \App\Models\Setting::getValue('bank_name'),
                    'bank_account' => \App\Models\Setting::getValue('bank_account_number'),
                    'bank_owner' => \App\Models\Setting::getValue('bank_account_name'),
                    'bank_branch' => \App\Models\Setting::getValue('bank_branch'),
                    'ewallet_provider' => \App\Models\Setting::getValue('ewallet_provider'),
                    'ewallet_number' => \App\Models\Setting::getValue('ewallet_number'),
                    'ewallet_name' => \App\Models\Setting::getValue('ewallet_name'),
                    'qris_image' => \App\Models\Setting::getValue('qris_image'),
                    'payment_instructions' => \App\Models\Setting::getValue('payment_instructions'),
                    'fiscal_year_start_month' => \App\Models\Setting::getValue('fiscal_year_start_month', 1),
                    'payment_deadline_days' => \App\Models\Setting::getValue('payment_deadline_days', 30),
                    'late_payment_penalty' => \App\Models\Setting::getValue('late_payment_penalty', false),
                    'late_payment_penalty_amount' => \App\Models\Setting::getValue('late_payment_penalty_amount', 0),
                ],
                'system' => [
                    'default_member_status' => \App\Models\Setting::getValue('default_member_status', 'active'),
                    'pagination_per_page' => \App\Models\Setting::getValue('pagination_per_page', 15),
                    'max_upload_size' => \App\Models\Setting::getValue('max_upload_size', 2048),
                    'session_lifetime' => \App\Models\Setting::getValue('session_lifetime', 120),
                    'date_format' => \App\Models\Setting::getValue('date_format', 'd/m/Y'),
                    'backup_auto_enabled' => \App\Models\Setting::getValue('backup_auto_enabled', false),
                    'backup_frequency_days' => \App\Models\Setting::getValue('backup_frequency_days', 7),
                ],
                'theme' => \App\Models\Setting::getValue('app_theme_color', 'indigo'),
                'locale' => app()->getLocale(),
                'timezone' => \App\Models\Setting::getValue('app_timezone', config('app.timezone')),
            ],
            'notifications' => $request->user() ? \App\Models\Announcement::published()
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn($a) => [
                    'id' => $a->id,
                    'title' => 'Pengumuman Baru',
                    'message' => $a->title,
                    'type' => 'announcement',
                    'created_at' => $a->created_at,
                    'link' => route('announcements.show', $a->id),
                ]) : [],
        ];
    }
}
