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
     * Create a new backup.
     */
    public function create()
    {
        try {
            // Run backup in background or wait for it
            Artisan::call('backup:run', ['--only-db' => true]);
            
            return back()->with('success', 'Backup berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    /**
     * Download the specified backup.
     */
    public function download($fileName)
    {
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
