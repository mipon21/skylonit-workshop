<?php

namespace App\Providers;

use App\Events\BugStatusUpdated;
use App\Events\ClientCreated;
use App\Events\DocumentUploaded;
use App\Events\ExpenseCreated;
use App\Events\LinkCreated;
use App\Events\NoteCreated;
use App\Events\PaymentCreated;
use App\Events\PaymentSuccess;
use App\Events\ProjectCreated;
use App\Events\TaskStatusUpdated;
use App\Listeners\SendBugResolvedNotification;
use App\Listeners\SendClientCreatedNotification;
use App\Listeners\SendDocumentUploadedNotification;
use App\Listeners\SendExpenseCreatedNotification;
use App\Listeners\SendLinkCreatedNotification;
use App\Listeners\SendNoteCreatedNotification;
use App\Listeners\SendPaymentCreatedNotification;
use App\Listeners\SendPaymentSuccessNotification;
use App\Listeners\SendProjectCreatedNotification;
use App\Listeners\SendTaskDoneNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ClientCreated::class => [SendClientCreatedNotification::class],
        ProjectCreated::class => [SendProjectCreatedNotification::class],
        PaymentCreated::class => [SendPaymentCreatedNotification::class],
        PaymentSuccess::class => [SendPaymentSuccessNotification::class],
        DocumentUploaded::class => [SendDocumentUploadedNotification::class],
        ExpenseCreated::class => [SendExpenseCreatedNotification::class],
        NoteCreated::class => [SendNoteCreatedNotification::class],
        LinkCreated::class => [SendLinkCreatedNotification::class],
        BugStatusUpdated::class => [SendBugResolvedNotification::class],
        TaskStatusUpdated::class => [SendTaskDoneNotification::class],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
