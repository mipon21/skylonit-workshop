<?php

namespace App\Listeners;

use App\Events\ClientCreated;
use App\Jobs\SendTemplateMailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendClientCreatedNotification implements ShouldQueue
{
    public function handle(ClientCreated $event): void
    {
        if (! $event->sendEmail) {
            return;
        }

        $client = $event->client;
        $email = $client->user?->email ?? $client->email;
        if (! $email) {
            return;
        }

        $loginUrl = route('login');

        SendTemplateMailJob::dispatch(
            'client_account_created',
            $email,
            [
                'client_name' => $client->name,
                'client_email' => $email,
                'login_url' => $loginUrl,
                'client_password' => $event->plainPassword,
            ]
        );
    }
}
