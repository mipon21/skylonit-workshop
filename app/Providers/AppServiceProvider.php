<?php

namespace App\Providers;

use App\Models\Bug;
use App\Models\ClientNotification;
use App\Models\Document;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectLink;
use App\Models\ProjectNote;
use App\Models\Setting;
use App\Models\Task;
use App\Observers\BugObserver;
use App\Observers\ClientNotificationObserver;
use App\Observers\DocumentObserver;
use App\Observers\ExpenseObserver;
use App\Observers\InvoiceObserver;
use App\Observers\PaymentObserver;
use App\Observers\ProjectActivityObserver;
use App\Observers\ProjectLinkObserver;
use App\Observers\ProjectNoteObserver;
use App\Observers\ProjectObserver;
use App\Observers\TaskObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
        Paginator::defaultView('vendor.pagination.tailwind');

        if (Schema::hasTable('settings')) {
            $logoPath = Setting::get('app_logo');
            $appLogoUrl = $logoPath ? asset('storage/' . $logoPath) : (config('app.logo') ? asset(ltrim(config('app.logo'), '/')) : null);
            View::share('appLogoUrl', $appLogoUrl);
            $faviconPath = Setting::get('app_favicon');
            View::share('appFaviconUrl', $faviconPath ? asset('storage/' . $faviconPath) : null);
        } else {
            View::share('appLogoUrl', config('app.logo') ? asset(ltrim(config('app.logo'), '/')) : null);
            View::share('appFaviconUrl', null);
        }

        Project::observe(ProjectObserver::class);
        ProjectActivity::observe(ProjectActivityObserver::class);
        Payment::observe(PaymentObserver::class);
        Expense::observe(ExpenseObserver::class);
        Document::observe(DocumentObserver::class);
        Task::observe(TaskObserver::class);
        Bug::observe(BugObserver::class);
        ProjectNote::observe(ProjectNoteObserver::class);
        ProjectLink::observe(ProjectLinkObserver::class);
        Invoice::observe(InvoiceObserver::class);
        ClientNotification::observe(ClientNotificationObserver::class);

        View::composer('*', function ($view) {
            $name = $view->getName();
            if ($name !== 'layouts.app' && $name !== 'layout.app') {
                return;
            }
            $user = auth()->user();
            $initialUnread = [];
            if ($user && $user->isClient() && $user->client) {
                $notifications = ClientNotification::where('client_id', $user->client->id)
                    ->where('is_read', false)
                    ->with(['project:id,project_name', 'payment:id,amount,payment_link,payment_status', 'payment.invoice:id,payment_id,invoice_number', 'invoice:id,invoice_number'])
                    ->orderByDesc('created_at')
                    ->get();
                $initialUnread = $notifications->map(function (ClientNotification $n) {
                    $item = [
                        'id' => $n->id,
                        'type' => $n->type,
                        'title' => $n->title,
                        'message' => $n->message,
                        'link' => $n->link,
                        'created_at' => $n->created_at->toIso8601String(),
                    ];
                    if ($n->project) {
                        $item['project_name'] = $n->project->project_name;
                    }
                    if ($n->payment) {
                        $item['amount'] = $n->payment->amount;
                        $item['payment_link'] = $n->payment->payment_link;
                        $item['payment_status'] = $n->payment->payment_status;
                        $item['invoice_view_url'] = $n->payment->invoice
                            ? route('invoices.view', $n->payment->invoice)
                            : null;
                    }
                    if ($n->invoice) {
                        $item['invoice_number'] = $n->invoice->invoice_number;
                        $item['invoice_view_url'] = route('invoices.view', $n->invoice);
                    }
                    return $item;
                })->values()->all();
            }
            $unreadCount = $user && $user->isClient() && $user->client
                ? \App\Models\ClientNotification::where('client_id', $user->client->id)->where('is_read', false)->count()
                : 0;
            $view->with('clientUnreadNotifications', $initialUnread);
            $view->with('clientUnreadCount', $unreadCount);
        });
    }
}
