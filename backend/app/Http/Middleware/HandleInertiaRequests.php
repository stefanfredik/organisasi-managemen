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
                'name' => config('app.name', 'Sistem Manajemen Organisasi'),
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
