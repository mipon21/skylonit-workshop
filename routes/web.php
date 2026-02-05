<?php

use App\Http\Controllers\BugController;
use App\Http\Controllers\CalendarNoteController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientPaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmailFooterController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
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

// Payment gateway return URLs – must be reachable by guests (client may pay without being logged in)
Route::match(['get', 'post'], '/client/payment/success', [ClientPaymentController::class, 'success'])->name('client.payment.success');
Route::get('/client/payment/cancel', [ClientPaymentController::class, 'cancel'])->name('client.payment.cancel');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/dashboard/calendar-notes', [CalendarNoteController::class, 'index'])->name('calendar-notes.index');
    Route::post('/dashboard/calendar-notes', [CalendarNoteController::class, 'store'])->name('calendar-notes.store');
    Route::delete('/dashboard/calendar-notes/{date}', [CalendarNoteController::class, 'destroy'])->name('calendar-notes.destroy');

    // Invoices: both admin and client can view/download their invoices
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::get('/invoices/{invoice}/view', [InvoiceController::class, 'view'])->name('invoices.view');

    // Client payments list (requires auth)
    Route::get('/client/payments', [ClientPaymentController::class, 'index'])->name('client.payments.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/logo', [ProfileController::class, 'updateLogo'])->name('profile.logo.update');
    Route::post('/profile/favicon', [ProfileController::class, 'updateFavicon'])->name('profile.favicon.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin-only routes
    Route::middleware('admin')->group(function () {
        Route::get('/revenue', [RevenueController::class, 'index'])->name('revenue.index');
        Route::resource('clients', ClientController::class);

        Route::get('projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        Route::patch('projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.status.update');

        Route::post('projects/{project}/payments', [PaymentController::class, 'store'])->name('projects.payments.store');
        Route::patch('projects/{project}/payments/{payment}', [PaymentController::class, 'update'])->name('projects.payments.update');
        Route::post('projects/{project}/payments/{payment}/mark-paid-cash', [PaymentController::class, 'markAsPaidCash'])->name('projects.payments.mark-paid-cash');
        Route::post('projects/{project}/payments/{payment}/generate-link', [PaymentController::class, 'generateLink'])->name('projects.payments.generate-link');
        Route::post('projects/{project}/payments/{payment}/send-payment-link-email', [PaymentController::class, 'sendPaymentLinkEmail'])->name('projects.payments.send-payment-link-email');
        Route::delete('projects/{project}/payments/{payment}', [PaymentController::class, 'destroy'])->name('projects.payments.destroy');
        Route::patch('projects/{project}/payouts', [ProjectPayoutController::class, 'update'])->name('projects.payouts.update');

        Route::post('projects/{project}/expenses', [ExpenseController::class, 'store'])->name('projects.expenses.store');
        Route::patch('projects/{project}/expenses/{expense}', [ExpenseController::class, 'update'])->name('projects.expenses.update');
        Route::delete('projects/{project}/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('projects.expenses.destroy');

        Route::patch('projects/{project}/documents/{document}', [DocumentController::class, 'update'])->name('projects.documents.update');

        Route::post('projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
        Route::patch('projects/{project}/tasks/{task}', [TaskController::class, 'update'])->name('projects.tasks.update');
        Route::delete('projects/{project}/tasks/{task}', [TaskController::class, 'destroy'])->name('projects.tasks.destroy');

        Route::patch('projects/{project}/bugs/{bug}', [BugController::class, 'update'])->name('projects.bugs.update');
        Route::delete('projects/{project}/bugs/{bug}', [BugController::class, 'destroy'])->name('projects.bugs.destroy');

        Route::post('projects/{project}/notes', [ProjectNoteController::class, 'store'])->name('projects.notes.store');
        Route::patch('projects/{project}/notes/{project_note}', [ProjectNoteController::class, 'update'])->name('projects.notes.update');
        Route::delete('projects/{project}/notes/{project_note}', [ProjectNoteController::class, 'destroy'])->name('projects.notes.destroy');

        Route::post('projects/{project}/links', [ProjectLinkController::class, 'store'])->name('projects.links.store');
        Route::patch('projects/{project}/links/{project_link}', [ProjectLinkController::class, 'update'])->name('projects.links.update');
        Route::delete('projects/{project}/links/{project_link}', [ProjectLinkController::class, 'destroy'])->name('projects.links.destroy');

        // Settings → Email Templates
        Route::get('settings/email-templates', [EmailTemplateController::class, 'index'])->name('email-templates.index');
        Route::get('settings/email-templates/{email_template}/edit', [EmailTemplateController::class, 'edit'])->name('email-templates.edit');
        Route::get('settings/email-templates/{email_template}/preview', [EmailTemplateController::class, 'preview'])->name('email-templates.preview');
        Route::put('settings/email-templates/{email_template}', [EmailTemplateController::class, 'update'])->name('email-templates.update');
        Route::get('settings/email-footer', [EmailFooterController::class, 'index'])->name('email-footer.index');
        Route::put('settings/email-footer', [EmailFooterController::class, 'update'])->name('email-footer.update');
    });

    // Shared: projects index & show (controller scopes for client)
    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

    // Documents: client can upload/view/download/delete for own projects
    Route::post('projects/{project}/documents', [DocumentController::class, 'store'])->name('projects.documents.store');
    Route::get('projects/{project}/documents/{document}/view', [DocumentController::class, 'view'])->name('projects.documents.view');
    Route::get('projects/{project}/documents/{document}/download', [DocumentController::class, 'download'])->name('projects.documents.download');
    Route::delete('projects/{project}/documents/{document}', [DocumentController::class, 'destroy'])->name('projects.documents.destroy');

    // Bugs: client can create and view attachment
    Route::post('projects/{project}/bugs', [BugController::class, 'store'])->name('projects.bugs.store');
    Route::get('projects/{project}/bugs/{bug}/attachment', [BugController::class, 'downloadAttachment'])->name('projects.bugs.attachment');
});

require __DIR__.'/auth.php';
