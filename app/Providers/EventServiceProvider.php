<?php

namespace App\Providers;

use App\Events\BugAssigned;
use App\Events\BugStatusUpdated;
use App\Events\ClientCreated;
use App\Events\DocumentUploaded;
use App\Events\InternalUserCreated;
use App\Events\ExpenseCreated;
use App\Events\LinkCreated;
use App\Events\NoteCreated;
use App\Events\PaymentCreated;
use App\Events\PaymentSuccess;
use App\Events\PayoutStatusChanged;
use App\Events\ProjectCreated;
use App\Events\ProjectStatusChanged;
use App\Events\TaskAssigned;
use App\Events\TaskStatusUpdated;
use App\Listeners\SendBugAssignedNotification;
use App\Listeners\SendBugResolvedNotification;
use App\Listeners\SendClientCreatedNotification;
use App\Listeners\SendDocumentUploadedNotification;
use App\Listeners\SendInternalUserCreatedNotification;
use App\Listeners\SendExpenseCreatedNotification;
use App\Listeners\SendLinkCreatedNotification;
use App\Listeners\NotifyDevelopersOnDocumentUploaded;
use App\Listeners\NotifyDevelopersOnLinkCreated;
use App\Listeners\NotifyDevelopersOnNoteCreated;
use App\Listeners\SendNoteCreatedNotification;
use App\Listeners\SendPaymentCreatedNotification;
use App\Listeners\SendPaymentSuccessNotification;
use App\Listeners\SendProjectCompleteToSalesNotification;
use App\Listeners\SendProjectCreatedNotification;
use App\Listeners\SendPayoutStatusChangedNotification;
use App\Listeners\SendTaskAssignedNotification;
use App\Listeners\SendMilestoneCompletedNotification;
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
        InternalUserCreated::class => [SendInternalUserCreatedNotification::class],
        ProjectCreated::class => [SendProjectCreatedNotification::class],
        PaymentCreated::class => [SendPaymentCreatedNotification::class],
        PaymentSuccess::class => [SendPaymentSuccessNotification::class],
        DocumentUploaded::class => [
            SendDocumentUploadedNotification::class,
            NotifyDevelopersOnDocumentUploaded::class,
        ],
        ExpenseCreated::class => [SendExpenseCreatedNotification::class],
        NoteCreated::class => [
            SendNoteCreatedNotification::class,
            NotifyDevelopersOnNoteCreated::class,
        ],
        LinkCreated::class => [
            SendLinkCreatedNotification::class,
            NotifyDevelopersOnLinkCreated::class,
        ],
        BugStatusUpdated::class => [SendBugResolvedNotification::class],
        TaskStatusUpdated::class => [
            SendMilestoneCompletedNotification::class,
            SendTaskDoneNotification::class,
        ],
        TaskAssigned::class => [SendTaskAssignedNotification::class],
        BugAssigned::class => [SendBugAssignedNotification::class],
        ProjectStatusChanged::class => [SendProjectCompleteToSalesNotification::class],
        PayoutStatusChanged::class => [SendPayoutStatusChangedNotification::class],
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
