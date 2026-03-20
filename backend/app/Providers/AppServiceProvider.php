<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS for URLs when APP_URL uses HTTPS
        if (str_starts_with(config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);

        // Register event listeners for activity logging
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            \App\Listeners\LogSuccessfulLogin::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Logout::class,
            \App\Listeners\LogSuccessfulLogout::class,
        );

        // Backup Process Status Trackers
        \Illuminate\Support\Facades\Event::listen(function (\Spatie\Backup\Events\BackupManifestWasCreated $event) {
            \Illuminate\Support\Facades\Cache::put('backup_process_message', 'Mengekspor & mengompresi database ke format ZIP...', now()->addMinutes(5));
        });

        \Illuminate\Support\Facades\Event::listen(function (\Spatie\Backup\Events\BackupWasSuccessful $event) {
            \Illuminate\Support\Facades\Cache::put('backup_process_status', 'completed', now()->addMinutes(5));
            \Illuminate\Support\Facades\Cache::put('backup_process_message', 'Pencadangan berhasil diselesaikan pada ' . now()->format('H:i:s'), now()->addMinutes(5));
        });

        \Illuminate\Support\Facades\Event::listen(function (\Spatie\Backup\Events\BackupHasFailed $event) {
            \Illuminate\Support\Facades\Cache::put('backup_process_status', 'failed', now()->addMinutes(5));
            \Illuminate\Support\Facades\Cache::put('backup_process_message', 'Gagal: ' . $event->exception->getMessage(), now()->addMinutes(5));
        });
    }
}
