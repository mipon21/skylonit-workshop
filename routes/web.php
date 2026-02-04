<?php

use App\Http\Controllers\BugController;
use App\Http\Controllers\CalendarNoteController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectPayoutController;
use App\Http\Controllers\ProjectLinkController;
use App\Http\Controllers\ProjectNoteController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/dashboard/calendar-notes', [CalendarNoteController::class, 'index'])->name('calendar-notes.index');
    Route::post('/dashboard/calendar-notes', [CalendarNoteController::class, 'store'])->name('calendar-notes.store');
    Route::delete('/dashboard/calendar-notes/{date}', [CalendarNoteController::class, 'destroy'])->name('calendar-notes.destroy');

    Route::get('/revenue', [RevenueController::class, 'index'])->name('revenue.index');

    Route::resource('clients', ClientController::class);

    Route::resource('projects', ProjectController::class);
    Route::patch('projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.status.update');

    Route::post('projects/{project}/payments', [PaymentController::class, 'store'])->name('projects.payments.store');
    Route::delete('projects/{project}/payments/{payment}', [PaymentController::class, 'destroy'])->name('projects.payments.destroy');

    Route::patch('projects/{project}/payouts', [ProjectPayoutController::class, 'update'])->name('projects.payouts.update');

    Route::post('projects/{project}/expenses', [ExpenseController::class, 'store'])->name('projects.expenses.store');
    Route::delete('projects/{project}/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('projects.expenses.destroy');

    Route::post('projects/{project}/documents', [DocumentController::class, 'store'])->name('projects.documents.store');
    Route::get('projects/{project}/documents/{document}/view', [DocumentController::class, 'view'])->name('projects.documents.view');
    Route::get('projects/{project}/documents/{document}/download', [DocumentController::class, 'download'])->name('projects.documents.download');
    Route::delete('projects/{project}/documents/{document}', [DocumentController::class, 'destroy'])->name('projects.documents.destroy');

    Route::post('projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
    Route::patch('projects/{project}/tasks/{task}', [TaskController::class, 'update'])->name('projects.tasks.update');
    Route::delete('projects/{project}/tasks/{task}', [TaskController::class, 'destroy'])->name('projects.tasks.destroy');

    Route::post('projects/{project}/bugs', [BugController::class, 'store'])->name('projects.bugs.store');
    Route::get('projects/{project}/bugs/{bug}/attachment', [BugController::class, 'downloadAttachment'])->name('projects.bugs.attachment');
    Route::patch('projects/{project}/bugs/{bug}', [BugController::class, 'update'])->name('projects.bugs.update');
    Route::delete('projects/{project}/bugs/{bug}', [BugController::class, 'destroy'])->name('projects.bugs.destroy');

    Route::post('projects/{project}/notes', [ProjectNoteController::class, 'store'])->name('projects.notes.store');
    Route::patch('projects/{project}/notes/{project_note}', [ProjectNoteController::class, 'update'])->name('projects.notes.update');
    Route::delete('projects/{project}/notes/{project_note}', [ProjectNoteController::class, 'destroy'])->name('projects.notes.destroy');

    Route::post('projects/{project}/links', [ProjectLinkController::class, 'store'])->name('projects.links.store');
    Route::patch('projects/{project}/links/{project_link}', [ProjectLinkController::class, 'update'])->name('projects.links.update');
    Route::delete('projects/{project}/links/{project_link}', [ProjectLinkController::class, 'destroy'])->name('projects.links.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/logo', [ProfileController::class, 'updateLogo'])->name('profile.logo.update');
    Route::post('/profile/favicon', [ProfileController::class, 'updateFavicon'])->name('profile.favicon.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
