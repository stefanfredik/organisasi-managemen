<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Public Event Routes
Route::get('/events/public', [\App\Http\Controllers\PublicEventController::class, 'index'])->name('public.events.index');
Route::get('/events/public/{slug}', [\App\Http\Controllers\PublicEventController::class, 'show'])->name('public.events.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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

    // Financial Management
    Route::resource('wallets', \App\Http\Controllers\WalletController::class);
    Route::resource('finances', \App\Http\Controllers\FinanceController::class);
    Route::resource('contribution-types', \App\Http\Controllers\ContributionTypeController::class);
    Route::resource('contributions', \App\Http\Controllers\ContributionController::class);
    Route::get('contributions/unpaid-members', [\App\Http\Controllers\ContributionController::class, 'getUnpaidMembers'])->name('contributions.unpaid-members');
    Route::post('contributions/{contribution}/verify', [\App\Http\Controllers\ContributionController::class, 'verify'])->name('contributions.verify');
});

require __DIR__ . '/auth.php';
