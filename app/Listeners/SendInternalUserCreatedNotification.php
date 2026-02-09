<?php

namespace App\Listeners;

use App\Events\InternalUserCreated;
use App\Jobs\SendTemplateMailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendInternalUserCreatedNotification implements ShouldQueue
{
    public function handle(InternalUserCreated $event): void
    {
        if (! $event->sendEmail) {
            return;
        }

        $user = $event->user;
        if (! $user->email) {
            return;
        }

        SendTemplateMailJob::dispatch(
            'internal_account_created',
            $user->email,
            [
                'name' => $user->name,
                'email' => $user->email,
                'login_url' => route('login'),
                'password' => $event->plainPassword,
            ]
        );
    }
}
