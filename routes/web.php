<?php

use App\Http\Controllers\BugController;
use App\Http\Controllers\ClientDeviceController;
use App\Http\Controllers\ClientNotificationController;
use App\Http\Controllers\CalendarNoteController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ClientPaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmailFooterController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\GoogleSyncController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\Guest\GuestDashboardController;
use App\Http\Controllers\Guest\GuestLinkController;
use App\Http\Controllers\Guest\GuestProjectController;
use App\Http\Controllers\Guest\LeadController as GuestLeadController;
use App\Http\Controllers\HotOfferController;
use App\Http\Controllers\InternalExpenseController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectPayoutController;
use App\Http\Controllers\ProjectLinkController;
use App\Http\Controllers\ProjectNoteController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TestimonialController;
use Illuminate\Support\Facades\Route;

// ========== Guest (public) portal – no auth ==========
Route::get('/', GuestDashboardController::class)->name('guest.dashboard');
Route::get('/projects', [GuestProjectController::class, 'index'])->name('guest.projects.index');
Route::get('/projects/{project}', [GuestProjectController::class, 'show'])->name('guest.projects.show');
Route::get('/links', [GuestLinkController::class, 'index'])->name('guest.links.index');
Route::get('/links/download/{project_link}', [GuestLinkController::class, 'download'])->name('guest.links.download');
Route::get('/contact', [GuestLeadController::class, 'create'])->name('guest.contact');
Route::post('/contact', [GuestLeadController::class, 'store'])->name('guest.contact.store');

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

    // Client notifications (popups) – for client users only; used by app layout JS
    Route::get('/client/notifications/unread', [ClientNotificationController::class, 'unread'])->name('client.notifications.unread');
    Route::patch('/client/notifications/{client_notification}/read', [ClientNotificationController::class, 'markRead'])->name('client.notifications.mark-read');

    // Client FCM device registration (push notifications)
    Route::post('/client/devices/register', [ClientDeviceController::class, 'register'])->name('client.devices.register');
    Route::post('/client/devices/unregister', [ClientDeviceController::class, 'unregister'])->name('client.devices.unregister');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/logo', [ProfileController::class, 'updateLogo'])->name('profile.logo.update');
    Route::post('/profile/favicon', [ProfileController::class, 'updateFavicon'])->name('profile.favicon.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin-only routes
    Route::middleware('admin')->group(function () {
        Route::get('/revenue', [RevenueController::class, 'index'])->name('revenue.index');
        Route::get('finance/investors', [InvestmentController::class, 'index'])->name('investments.index');
        Route::get('finance/investors/create', [InvestmentController::class, 'create'])->name('investments.create');
        Route::post('finance/investors', [InvestmentController::class, 'store'])->name('investments.store');
        Route::get('finance/investors/profit-pool', [InvestmentController::class, 'profitPool'])->name('investments.profit-pool');
        Route::post('finance/investors/run-distribution', [InvestmentController::class, 'runDistribution'])->name('investments.run-distribution');
        Route::get('finance/investors/{investment}', [InvestmentController::class, 'show'])->name('investments.show');
        Route::get('finance/investors/{investment}/edit', [InvestmentController::class, 'edit'])->name('investments.edit');
        Route::put('finance/investors/{investment}', [InvestmentController::class, 'update'])->name('investments.update');
        Route::delete('finance/investors/{investment}', [InvestmentController::class, 'destroy'])->name('investments.destroy');
        Route::get('finance/internal-expenses', [InternalExpenseController::class, 'index'])->name('internal-expenses.index');
        Route::get('finance/internal-expenses/create', [InternalExpenseController::class, 'create'])->name('internal-expenses.create');
        Route::post('finance/internal-expenses', [InternalExpenseController::class, 'store'])->name('internal-expenses.store');
        Route::get('finance/internal-expenses/ledger', [InternalExpenseController::class, 'ledger'])->name('internal-expenses.ledger');
        Route::get('finance/internal-expenses/{internal_expense}/edit', [InternalExpenseController::class, 'edit'])->name('internal-expenses.edit');
        Route::put('finance/internal-expenses/{internal_expense}', [InternalExpenseController::class, 'update'])->name('internal-expenses.update');
        Route::delete('finance/internal-expenses/{internal_expense}', [InternalExpenseController::class, 'destroy'])->name('internal-expenses.destroy');
        Route::get('finance/internal-expenses/report/overhead', [InternalExpenseController::class, 'reportOverhead'])->name('internal-expenses.report.overhead');
        Route::get('finance/internal-expenses/report/investment', [InternalExpenseController::class, 'reportInvestment'])->name('internal-expenses.report.investment');
        Route::get('/marketing/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('/marketing/leads/export', [LeadController::class, 'export'])->name('leads.export');
        Route::get('/marketing/leads/export/xlsx', [LeadController::class, 'exportExcel'])->name('leads.export.xlsx');
        Route::patch('/marketing/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
        Route::resource('marketing/hot-offers', HotOfferController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->names([
            'index' => 'hot-offers.index',
            'create' => 'hot-offers.create',
            'store' => 'hot-offers.store',
            'edit' => 'hot-offers.edit',
            'update' => 'hot-offers.update',
            'destroy' => 'hot-offers.destroy',
        ]);
        Route::resource('marketing/testimonials', TestimonialController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->names([
            'index' => 'testimonials.index',
            'create' => 'testimonials.create',
            'store' => 'testimonials.store',
            'edit' => 'testimonials.edit',
            'update' => 'testimonials.update',
            'destroy' => 'testimonials.destroy',
        ]);
        Route::resource('clients', ClientController::class);

        Route::get('dashboard/projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        Route::patch('projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.status.update');
        Route::patch('projects/{project}/client', [ProjectController::class, 'updateClient'])->name('projects.client.update');
        Route::post('projects/{project}/additional-clients', [ProjectController::class, 'addClient'])->name('projects.additional-clients.store');
        Route::delete('projects/{project}/additional-clients/{client}', [ProjectController::class, 'removeClient'])->name('projects.additional-clients.destroy');

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

        Route::post('projects/{project}/contracts', [ContractController::class, 'store'])->name('projects.contracts.store');
        Route::delete('projects/{project}/contracts/{contract}', [ContractController::class, 'destroy'])->name('projects.contracts.destroy');
        Route::post('projects/{project}/contracts/{contract}/send-email', [ContractController::class, 'sendEmail'])->name('projects.contracts.send-email');

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
        Route::get('settings/google-sync', [GoogleSyncController::class, 'index'])->name('google-sync.index');
        Route::post('settings/google-sync/sync-now', [GoogleSyncController::class, 'syncNow'])->name('google-sync.sync-now');
    });

    // Shared: projects index & show (controller scopes for client) – under dashboard to avoid conflict with guest /projects
    Route::get('dashboard/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('dashboard/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

    // Documents: client can upload/view/download/delete for own projects
    Route::post('projects/{project}/documents', [DocumentController::class, 'store'])->name('projects.documents.store');
    Route::get('projects/{project}/documents/{document}/view', [DocumentController::class, 'view'])->name('projects.documents.view');
    Route::get('projects/{project}/documents/{document}/download', [DocumentController::class, 'download'])->name('projects.documents.download');
    Route::delete('projects/{project}/documents/{document}', [DocumentController::class, 'destroy'])->name('projects.documents.destroy');

    // Project link APK download (admin or project client when link is visible to client)
    Route::get('projects/{project}/links/{project_link}/download', [ProjectLinkController::class, 'download'])->name('projects.links.download');

    Route::get('projects/{project}/contracts/{contract}/view', [ContractController::class, 'view'])->name('projects.contracts.view');
    Route::get('projects/{project}/contracts/{contract}/download', [ContractController::class, 'download'])->name('projects.contracts.download');
    Route::get('projects/{project}/contracts/{contract}/sign', [ContractController::class, 'signForm'])->name('projects.contracts.sign-form');
    Route::post('projects/{project}/contracts/{contract}/sign', [ContractController::class, 'sign'])->name('projects.contracts.sign');

    // Bugs: client can create and view attachment
    Route::post('projects/{project}/bugs', [BugController::class, 'store'])->name('projects.bugs.store');
    Route::get('projects/{project}/bugs/{bug}/attachment', [BugController::class, 'downloadAttachment'])->name('projects.bugs.attachment');
});

require __DIR__.'/auth.php';
