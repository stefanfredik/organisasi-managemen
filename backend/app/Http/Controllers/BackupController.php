<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $disk = Storage::disk('local');
        $backupFolderName = config('backup.backup.name');
        
        $files = $disk->allFiles($backupFolderName);
        
        $backups = [];
        foreach ($files as $file) {
            if (str_ends_with($file, '.zip')) {
                $backups[] = [
                    'file_name' => str_replace($backupFolderName . '/', '', $file),
                    'file_size' => $this->formatBytes($disk->size($file)),
                    'last_modified' => date('Y-m-d H:i:s', $disk->lastModified($file)),
                    'path' => $file,
                ];
            }
        }

        // Sort by last modified descending
        usort($backups, function ($a, $b) {
            return strcmp($b['last_modified'], $a['last_modified']);
        });

        return Inertia::render('Administration/Backups/Index', [
            'backups' => $backups,
        ]);
    }

    /**
     * Get the current backup status from cache.
     */
    public function status()
    {
        $status = \Illuminate\Support\Facades\Cache::get('backup_process_status', 'idle');
        $message = \Illuminate\Support\Facades\Cache::get('backup_process_message', '');

        // Auto-reset stale "running" state after 10 minutes
        if ($status === 'running') {
            $startedAt = \Illuminate\Support\Facades\Cache::get('backup_process_started_at');
            if ($startedAt && now()->diffInMinutes($startedAt) >= 10) {
                \Illuminate\Support\Facades\Cache::put('backup_process_status', 'failed', now()->addMinutes(5));
                \Illuminate\Support\Facades\Cache::put('backup_process_message', 'Proses pencadangan melebihi batas waktu (timeout).', now()->addMinutes(5));
                $status = 'failed';
                $message = 'Proses pencadangan melebihi batas waktu (timeout).';
            }
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
        ]);
    }

    /**
     * Create a new backup.
     */
    public function create()
    {
        // Don't start another backup if one is already running
        if (\Illuminate\Support\Facades\Cache::get('backup_process_status') === 'running') {
            return back()->with('error', 'Proses pencadangan sudah berjalan.');
        }

        try {
            \Illuminate\Support\Facades\Cache::put('backup_process_status', 'running', now()->addMinutes(15));
            \Illuminate\Support\Facades\Cache::put('backup_process_message', 'Memulai inisialisasi modul pencadangan...', now()->addMinutes(15));
            \Illuminate\Support\Facades\Cache::put('backup_process_started_at', now(), now()->addMinutes(15));

            $artisan = base_path('artisan');
            $phpBinary = PHP_BINARY ?: 'php';

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                pclose(popen("start /B \"$phpBinary\" \"$artisan\" backup:run --only-db", "r"));
            } else {
                exec("\"$phpBinary\" \"$artisan\" backup:run --only-db > /dev/null 2>&1 &");
            }

            return back()->with('success', 'Proses pencadangan latar belakang dimulai.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Cache::forget('backup_process_status');
            \Illuminate\Support\Facades\Cache::forget('backup_process_message');
            \Illuminate\Support\Facades\Cache::forget('backup_process_started_at');
            return back()->with('error', 'Gagal memulai pencadangan: ' . $e->getMessage());
        }
    }

    /**
     * Download the specified backup.
     */
    public function download($fileName)
    {
        $fileName = basename($fileName);
        $backupFolderName = config('backup.backup.name');
        $filePath = $backupFolderName . '/' . $fileName;

        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->download($filePath);
        }

        return back()->with('error', 'File backup tidak ditemukan.');
    }

    /**
     * Remove the specified backup from storage.
     */
    public function destroy($fileName)
    {
        $fileName = basename($fileName);
        $backupFolderName = config('backup.backup.name');
        $filePath = $backupFolderName . '/' . $fileName;

        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
            return back()->with('success', 'Backup berhasil dihapus.');
        }

        return back()->with('error', 'File backup tidak ditemukan.');
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
