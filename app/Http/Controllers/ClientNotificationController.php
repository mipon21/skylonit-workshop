<?php

namespace App\Http\Controllers;

use App\Models\ClientNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ClientNotificationController extends Controller
{
    /**
     * Return unread notifications for the authenticated client.
     * Used on dashboard load to show popups.
     */
    public function unread(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->isClient() || ! $user->client) {
            return response()->json(['notifications' => []]);
        }

        $notifications = ClientNotification::getLatestUnreadForPopups($user->client->id);
        $notifications->load(['project:id,project_name', 'payment:id,amount,payment_link,payment_status', 'payment.invoice:id,payment_id,invoice_number', 'invoice:id,invoice_number']);

        $list = $notifications->map(function (ClientNotification $n) {
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
            // Primary "Show" destination: invoice view or payments list
            $item['show_url'] = $item['invoice_view_url'] ?? route('client.payments.index');
            return $item;
        });

        return response()->json(['notifications' => $list->values()]);
    }

    /**
     * Mark a notification as read. Only the owning client can mark.
     */
    public function markRead(Request $request, ClientNotification $client_notification): JsonResponse|Response
    {
        $user = $request->user();
        if (! $user->isClient() || ! $user->client) {
            abort(403);
        }
        if ($client_notification->client_id !== $user->client->id) {
            abort(403);
        }

        $client_notification->markAsRead();

        return request()->wantsJson()
            ? response()->json(['ok' => true])
            : response()->noContent();
    }
}
