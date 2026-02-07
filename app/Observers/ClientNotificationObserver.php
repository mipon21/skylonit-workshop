<?php

namespace App\Observers;

use App\Models\ClientNotification;
use App\Services\FcmService;

class ClientNotificationObserver
{
    public function __construct(
        protected FcmService $fcmService
    ) {}

    /**
     * After a ClientNotification is created, send FCM push to all client devices.
     * Does not affect existing notification flow; push is additive only.
     */
    public function created(ClientNotification $notification): void
    {
        try {
            $this->fcmService->sendToClientDevices($notification);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
