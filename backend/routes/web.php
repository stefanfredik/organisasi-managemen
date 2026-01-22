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
    Route::middleware('permission:manage_members')->group(function () {
        Route::resource('members', \App\Http\Controllers\MemberController::class)->except(['index', 'show']);
    });
    Route::middleware('permission:view_members')->group(function () {
        Route::get('members', [\App\Http\Controllers\MemberController::class, 'index'])->name('members.index');
        Route::get('members/{member}', [\App\Http\Controllers\MemberController::class, 'show'])->name('members.show');
    });

    // Event Management
    Route::get('events/calendar', [\App\Http\Controllers\EventController::class, 'calendar'])->name('events.calendar')->middleware('permission:view_events');

    Route::middleware('permission:manage_events')->group(function () {
        Route::resource('events', \App\Http\Controllers\EventController::class)->except(['index', 'show']);
        Route::patch('events/{event}/participants/{member}', [\App\Http\Controllers\EventController::class, 'updateParticipantStatus'])->name('events.participants.update-status');
        Route::post('events/{event}/documentations', [\App\Http\Controllers\EventController::class, 'uploadDocumentation'])->name('events.documentations.upload');
        Route::delete('events/{event}/documentations/{documentation}', [\App\Http\Controllers\EventController::class, 'deleteDocumentation'])->name('events.documentations.destroy');
    });

    Route::middleware('permission:view_events')->group(function () {
        Route::get('events', [\App\Http\Controllers\EventController::class, 'index'])->name('events.index');
        Route::get('events/{event}', [\App\Http\Controllers\EventController::class, 'show'])->name('events.show');
        // Allow members to join/leave events
        Route::post('events/{event}/participants', [\App\Http\Controllers\EventController::class, 'addParticipant'])->name('events.participants.add');
        Route::delete('events/{event}/participants/{member}', [\App\Http\Controllers\EventController::class, 'removeParticipant'])->name('events.participants.remove');
    });

    Route::middleware('permission:manage_finance')->group(function () {
        Route::resource('wallets', \App\Http\Controllers\WalletController::class)->except(['index']);
        Route::resource('finances', \App\Http\Controllers\FinanceController::class)->except(['index']);
    });

    Route::middleware('permission:view_finance')->group(function () {
        Route::get('wallets', [\App\Http\Controllers\WalletController::class, 'index'])->name('wallets.index');
        Route::get('finances', [\App\Http\Controllers\FinanceController::class, 'index'])->name('finances.index');
    });
    
    // Contribbution Types Management
    Route::middleware('permission:manage_contribution_types')->group(function () {
        Route::resource('contribution-types', \App\Http\Controllers\ContributionTypeController::class)->except(['index']);
    });

    Route::middleware('permission:view_contribution_types')->group(function () {
        Route::get('contribution-types', [\App\Http\Controllers\ContributionTypeController::class, 'index'])->name('contribution-types.index');
    });

    // Contribution Management
    Route::middleware('permission:manage_contributions')->group(function () {
        Route::get('contributions/unpaid-members', [\App\Http\Controllers\ContributionController::class, 'getUnpaidMembers'])->name('contributions.unpaid-members');
        Route::post('contributions/{contribution}/verify', [\App\Http\Controllers\ContributionController::class, 'verify'])->name('contributions.verify');
        
        Route::get('contributions/create', [\App\Http\Controllers\ContributionController::class, 'create'])->name('contributions.create');
        Route::get('contributions/{contribution}/edit', [\App\Http\Controllers\ContributionController::class, 'edit'])->name('contributions.edit');
        Route::put('contributions/{contribution}', [\App\Http\Controllers\ContributionController::class, 'update'])->name('contributions.update');
        Route::patch('contributions/{contribution}', [\App\Http\Controllers\ContributionController::class, 'update'])->name('contributions.update'); // Support PATCH too
        Route::delete('contributions/{contribution}', [\App\Http\Controllers\ContributionController::class, 'destroy'])->name('contributions.destroy');
    });

    Route::middleware('permission:view_contributions')->group(function () {
        // New Monitoring Routes (Must be before wildcards)
        // New Monitoring Routes
        Route::get('contributions/monitoring', [\App\Http\Controllers\ContributionController::class, 'monitoringList'])->name('contributions.monitoring.index');
        Route::get('contributions/verification', [\App\Http\Controllers\ContributionController::class, 'verification'])->name('contributions.verification');
        Route::post('contributions/verification/{id}', [\App\Http\Controllers\ContributionController::class, 'verifyAction'])->name('contributions.verify-action');
        
        // Specific Monitoring Routes (Dashboard, Matrix, History)
        Route::prefix('contributions/monitoring/{contributionType}')->name('contributions.monitoring.')->group(function() {
            Route::get('/dashboard', [\App\Http\Controllers\ContributionController::class, 'monitoringDashboard'])->name('dashboard');
            Route::get('/matrix', [\App\Http\Controllers\ContributionController::class, 'monitoringMatrix'])->name('matrix');
            Route::get('/matrix/export', [\App\Http\Controllers\ContributionController::class, 'exportMatrix'])->name('matrix.export');
            Route::get('/history', [\App\Http\Controllers\ContributionController::class, 'monitoringHistory'])->name('history');
        });

        Route::get('contributions', [\App\Http\Controllers\ContributionController::class, 'index'])->name('contributions.index');
        Route::get('contributions/{contribution}', [\App\Http\Controllers\ContributionController::class, 'show'])->name('contributions.show');
        Route::post('contributions', [\App\Http\Controllers\ContributionController::class, 'store'])->name('contributions.store');
        Route::post('contributions/bulk', [\App\Http\Controllers\ContributionController::class, 'storeBulk'])->name('contributions.bulk-store');
        Route::get('contributions/period-summary', [\App\Http\Controllers\ContributionController::class, 'periodSummary'])->name('contributions.period-summary');
        Route::get('contributions/{contributionType}/my-status', [\App\Http\Controllers\ContributionController::class, 'getMemberStatus'])->name('contributions.my-status');
        

    });

    // Donation Management
    Route::middleware('permission:manage_finance')->group(function () {
        Route::resource('donations', \App\Http\Controllers\DonationController::class)->except(['index', 'show']);
        Route::post('donations/{donation}/transactions', [\App\Http\Controllers\DonationController::class, 'recordTransaction'])->name('donations.transactions.store');
    });

    Route::middleware('permission:view_donations')->group(function () {
        Route::get('donations', [\App\Http\Controllers\DonationController::class, 'index'])->name('donations.index');
        Route::get('donations/{donation}', [\App\Http\Controllers\DonationController::class, 'show'])->name('donations.show');
        Route::get('donations/report', [\App\Http\Controllers\DonationController::class, 'report'])->name('donations.report');
    });

    // Album Management
    Route::middleware('permission:manage_albums')->group(function () {
        Route::resource('albums', \App\Http\Controllers\AlbumController::class)->except(['index', 'show']);
        Route::post('albums/{album}/photos', [\App\Http\Controllers\AlbumController::class, 'uploadPhotos'])->name('albums.photos.upload');
        Route::delete('albums/{album}/photos/{photo}', [\App\Http\Controllers\AlbumController::class, 'deletePhoto'])->name('albums.photos.destroy');
        Route::patch('albums/{album}/photos/order', [\App\Http\Controllers\AlbumController::class, 'updatePhotoOrder'])->name('albums.photos.update-order');
        Route::patch('albums/{album}/photos/{photo}/description', [\App\Http\Controllers\AlbumController::class, 'updatePhotoDescription'])->name('albums.photos.update-description');
    });

    Route::middleware('permission:view_albums')->group(function () {
        Route::get('albums', [\App\Http\Controllers\AlbumController::class, 'index'])->name('albums.index');
        Route::get('albums/{album}', [\App\Http\Controllers\AlbumController::class, 'show'])->name('albums.show');
    });

    // Photo Management
    Route::get('photos/{photo}', [\App\Http\Controllers\PhotoController::class, 'show'])->name('photos.show');
    Route::get('photos/{photo}/download', [\App\Http\Controllers\PhotoController::class, 'download'])->name('photos.download');

    // Vision & Mission Management
    Route::resource('vision-missions', \App\Http\Controllers\VisionMissionController::class);
    Route::patch('vision-missions/{visionMission}/toggle-status', [\App\Http\Controllers\VisionMissionController::class, 'toggleStatus'])->name('vision-missions.toggle-status');

    // Organization Structure Management
    Route::middleware('permission:manage_organization_structures')->group(function () {
        Route::resource('organization-structures', \App\Http\Controllers\OrganizationStructureController::class)->except(['index', 'show']);
    });

    Route::middleware('permission:view_organization_structures')->group(function () {
        Route::get('organization-structures', [\App\Http\Controllers\OrganizationStructureController::class, 'index'])->name('organization-structures.index');
    });

    // Administration - Announcements
    Route::middleware('permission:manage_announcements')->group(function () {
        Route::resource('announcements', \App\Http\Controllers\AnnouncementController::class)->except(['index', 'show']);
    });

    Route::middleware('permission:view_announcements')->group(function () {
        Route::get('announcements', [\App\Http\Controllers\AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('announcements/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'show'])->name('announcements.show');
    });

    // Administration - Meeting Minutes
    Route::middleware('permission:manage_meeting_minutes')->group(function () {
        Route::resource('meeting-minutes', \App\Http\Controllers\MeetingMinuteController::class)->except(['index', 'show']);
        Route::post('meeting-minutes/{minute}/attachments', [\App\Http\Controllers\MeetingMinuteController::class, 'uploadAttachment'])->name('meeting-minutes.attachments');
    });

    Route::middleware('permission:view_meeting_minutes')->group(function () {
        Route::get('meeting-minutes', [\App\Http\Controllers\MeetingMinuteController::class, 'index'])->name('meeting-minutes.index');
        Route::get('meeting-minutes/attachments/{attachment}/download', [\App\Http\Controllers\MeetingMinuteController::class, 'downloadAttachment'])->name('meeting-minutes.attachments.download');
        Route::get('meeting-minutes/{minute}', [\App\Http\Controllers\MeetingMinuteController::class, 'show'])->name('meeting-minutes.show');
    });

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
    Route::prefix('reports')->name('reports.')->middleware('permission:view_reports')->group(function () {
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

// Indonesian Area Proxy API (to avoid browser CORS)
Route::prefix('api/areas')->group(function () {
    Route::get('provinces', [\App\Http\Controllers\AreaController::class, 'provinces'])->name('api.areas.provinces');
    Route::get('regencies/{provinceCode}', [\App\Http\Controllers\AreaController::class, 'regencies'])->name('api.areas.regencies');
    Route::get('districts/{regencyCode}', [\App\Http\Controllers\AreaController::class, 'districts'])->name('api.areas.districts');
    Route::get('villages/{districtCode}', [\App\Http\Controllers\AreaController::class, 'villages'])->name('api.areas.villages');
});

require __DIR__ . '/auth.php';
