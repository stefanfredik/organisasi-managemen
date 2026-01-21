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
                    'status' => $request->user()->status,
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
                'email' => \App\Models\Setting::getValue('organization_email'),
                'phone' => \App\Models\Setting::getValue('organization_phone'),
                'address' => \App\Models\Setting::getValue('organization_address'),
                'welcome_text' => \App\Models\Setting::getValue('portal_welcome_text', 'Selamat Datang di Portal Resmi Organisasi Kami'),
                'meta_description' => \App\Models\Setting::getValue('portal_meta_description'),
                'social' => [
                    'facebook' => \App\Models\Setting::getValue('social_facebook'),
                    'instagram' => \App\Models\Setting::getValue('social_instagram'),
                    'twitter' => \App\Models\Setting::getValue('social_twitter'),
                    'youtube' => \App\Models\Setting::getValue('social_youtube'),
                    'whatsapp' => \App\Models\Setting::getValue('social_whatsapp'),
                ],
                'features' => [
                    'donations' => \App\Models\Setting::getValue('enable_donations', true),
                    'gallery' => \App\Models\Setting::getValue('enable_gallery', true),
                ],
                'financial' => [
                    'currency' => \App\Models\Setting::getValue('currency_symbol', 'Rp'),
                    'bank_name' => \App\Models\Setting::getValue('bank_name'),
                    'bank_account' => \App\Models\Setting::getValue('bank_account_number'),
                    'bank_owner' => \App\Models\Setting::getValue('bank_account_name'),
                ],
                'locale' => app()->getLocale(),
                'timezone' => config('app.timezone'),
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
