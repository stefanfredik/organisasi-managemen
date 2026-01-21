<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [\App\Http\Controllers\PublicController::class, 'home'])->name('home');
Route::get('/about', [\App\Http\Controllers\PublicController::class, 'about'])->name('public.about');
Route::get('/vision-mission', [\App\Http\Controllers\PublicController::class, 'visionMission'])->name('public.vision-mission');
Route::get('/structure', [\App\Http\Controllers\PublicController::class, 'structure'])->name('public.structure');
Route::get('/contact', [\App\Http\Controllers\PublicController::class, 'contact'])->name('public.contact');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Public Event Routes
Route::get('/events/public', [\App\Http\Controllers\PublicEventController::class, 'index'])->name('public.events.index');
Route::get('/events/public/{slug}', [\App\Http\Controllers\PublicEventController::class, 'show'])->name('public.events.show');
Route::get('/donations/public', [\App\Http\Controllers\PublicDonationController::class, 'index'])->name('public.donations.index');
Route::get('/donations/public/{slug}', [\App\Http\Controllers\PublicDonationController::class, 'show'])->name('public.donations.show');

// Public Album/Gallery Routes
Route::get('/gallery', [\App\Http\Controllers\PublicAlbumController::class, 'index'])->name('public.gallery.index');
Route::get('/gallery/{slug}', [\App\Http\Controllers\PublicAlbumController::class, 'show'])->name('public.gallery.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User Management
    Route::patch('users/{user}/toggle-status', [\App\Http\Controllers\UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('users/{user}/reset-password', [\App\Http\Controllers\UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::resource('users', \App\Http\Controllers\UserController::class);

    // Member Management
    Route::resource('members', \App\Http\Controllers\MemberController::class);

    // Event Management
    Route::get('events/calendar', [\App\Http\Controllers\EventController::class, 'calendar'])->name('events.calendar');
    Route::resource('events', \App\Http\Controllers\EventController::class);
    Route::post('events/{event}/participants', [\App\Http\Controllers\EventController::class, 'addParticipant'])->name('events.participants.add');
    Route::delete('events/{event}/participants/{member}', [\App\Http\Controllers\EventController::class, 'removeParticipant'])->name('events.participants.remove');
    Route::patch('events/{event}/participants/{member}', [\App\Http\Controllers\EventController::class, 'updateParticipantStatus'])->name('events.participants.update-status');
    Route::post('events/{event}/documentations', [\App\Http\Controllers\EventController::class, 'uploadDocumentation'])->name('events.documentations.upload');
    Route::delete('events/{event}/documentations/{documentation}', [\App\Http\Controllers\EventController::class, 'deleteDocumentation'])->name('events.documentations.destroy');

    Route::resource('wallets', \App\Http\Controllers\WalletController::class);
    Route::resource('finances', \App\Http\Controllers\FinanceController::class);
    Route::resource('contribution-types', \App\Http\Controllers\ContributionTypeController::class);

    // Custom contribution routes MUST come BEFORE the resource route
    Route::get('contributions/unpaid-members', [\App\Http\Controllers\ContributionController::class, 'getUnpaidMembers'])->name('contributions.unpaid-members');
    Route::post('contributions/{contribution}/verify', [\App\Http\Controllers\ContributionController::class, 'verify'])->name('contributions.verify');

    // Resource route comes AFTER custom routes
    Route::resource('contributions', \App\Http\Controllers\ContributionController::class);
    // Donation Management
    Route::get('donations/report', [\App\Http\Controllers\DonationController::class, 'report'])->name('donations.report');
    Route::post('donations/{donation}/transactions', [\App\Http\Controllers\DonationController::class, 'recordTransaction'])->name('donations.transactions.store');
    Route::resource('donations', \App\Http\Controllers\DonationController::class);

    // Album Management
    Route::resource('albums', \App\Http\Controllers\AlbumController::class);
    Route::post('albums/{album}/photos', [\App\Http\Controllers\AlbumController::class, 'uploadPhotos'])->name('albums.photos.upload');
    Route::delete('albums/{album}/photos/{photo}', [\App\Http\Controllers\AlbumController::class, 'deletePhoto'])->name('albums.photos.destroy');
    Route::patch('albums/{album}/photos/order', [\App\Http\Controllers\AlbumController::class, 'updatePhotoOrder'])->name('albums.photos.update-order');
    Route::patch('albums/{album}/photos/{photo}/description', [\App\Http\Controllers\AlbumController::class, 'updatePhotoDescription'])->name('albums.photos.update-description');

    // Photo Management
    Route::get('photos/{photo}', [\App\Http\Controllers\PhotoController::class, 'show'])->name('photos.show');
    Route::get('photos/{photo}/download', [\App\Http\Controllers\PhotoController::class, 'download'])->name('photos.download');

    // Vision & Mission Management
    Route::resource('vision-missions', \App\Http\Controllers\VisionMissionController::class);
    Route::patch('vision-missions/{visionMission}/toggle-status', [\App\Http\Controllers\VisionMissionController::class, 'toggleStatus'])->name('vision-missions.toggle-status');

    // Organization Structure Management
    Route::resource('organization-structures', \App\Http\Controllers\OrganizationStructureController::class);

    // Administration - Announcements
    Route::resource('announcements', \App\Http\Controllers\AnnouncementController::class);

    // Administration - Meeting Minutes
    Route::resource('meeting-minutes', \App\Http\Controllers\MeetingMinuteController::class);
    Route::post('meeting-minutes/{minute}/attachments', [\App\Http\Controllers\MeetingMinuteController::class, 'uploadAttachment'])->name('meeting-minutes.attachments');
    Route::get('meeting-minutes/attachments/{attachment}/download', [\App\Http\Controllers\MeetingMinuteController::class, 'downloadAttachment'])->name('meeting-minutes.attachments.download');

    // Administration - Activity Logs
    Route::get('activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Administration - Settings
    Route::get('settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');

    // Administration - Backups
    Route::get('backups', [\App\Http\Controllers\BackupController::class, 'index'])->name('backups.index');
    Route::post('backups', [\App\Http\Controllers\BackupController::class, 'create'])->name('backups.create');
    Route::get('backups/{fileName}/download', [\App\Http\Controllers\BackupController::class, 'download'])->name('backups.download');
    Route::delete('backups/{fileName}', [\App\Http\Controllers\BackupController::class, 'destroy'])->name('backups.destroy');

    // Administration - Documents
    Route::resource('documents', \App\Http\Controllers\DocumentController::class);
    Route::get('documents/{document}/download', [\App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');
    
    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
        Route::get('/financial', [\App\Http\Controllers\ReportController::class, 'financial'])->name('financial');
        Route::get('/financial/pdf', [\App\Http\Controllers\ReportController::class, 'financialPdf'])->name('financial.pdf');
        Route::get('/financial/excel', [\App\Http\Controllers\ReportController::class, 'financialExcel'])->name('financial.excel');
        Route::get('/cash-flow', [\App\Http\Controllers\ReportController::class, 'cashFlow'])->name('cash-flow');
        Route::get('/cash-flow/pdf', [\App\Http\Controllers\ReportController::class, 'cashFlowPdf'])->name('cash-flow.pdf');
        Route::get('/cash-flow/excel', [\App\Http\Controllers\ReportController::class, 'cashFlowExcel'])->name('cash-flow.excel');
        Route::get('/balance-sheet', [\App\Http\Controllers\ReportController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('/balance-sheet/pdf', [\App\Http\Controllers\ReportController::class, 'balanceSheetPdf'])->name('balance-sheet.pdf');
        Route::get('/contributions', [\App\Http\Controllers\ReportController::class, 'contributions'])->name('contributions');
        Route::get('/contributions/pdf', [\App\Http\Controllers\ReportController::class, 'contributionsPdf'])->name('contributions.pdf');
        Route::get('/contributions/excel', [\App\Http\Controllers\ReportController::class, 'contributionsExcel'])->name('contributions.excel');
        Route::get('/donations', [\App\Http\Controllers\ReportController::class, 'donations'])->name('donations');
        Route::get('/donations/pdf', [\App\Http\Controllers\ReportController::class, 'donationsPdf'])->name('donations.pdf');
        Route::get('/donations/excel', [\App\Http\Controllers\ReportController::class, 'donationsExcel'])->name('donations.excel');
    });
});

// Public Vision & Mission API
Route::get('/api/vision-mission/active', [\App\Http\Controllers\VisionMissionController::class, 'getActive'])->name('api.vision-mission.active');

require __DIR__ . '/auth.php';
