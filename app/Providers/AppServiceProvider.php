<?php

namespace App\Providers;

use App\Models\Bug;
use App\Models\Document;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectNote;
use App\Models\Setting;
use App\Models\Task;
use App\Observers\BugObserver;
use App\Observers\DocumentObserver;
use App\Observers\ExpenseObserver;
use App\Observers\PaymentObserver;
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
        Payment::observe(PaymentObserver::class);
        Expense::observe(ExpenseObserver::class);
        Document::observe(DocumentObserver::class);
        Task::observe(TaskObserver::class);
        Bug::observe(BugObserver::class);
        ProjectNote::observe(ProjectNoteObserver::class);
    }
}
