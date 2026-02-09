<?php

namespace App\Http\Controllers;

use App\Events\PayoutStatusChanged;
use App\Models\Project;
use App\Models\ProjectPayout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectPayoutController extends Controller
{
    public function update(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:overhead,sales,developer,profit'],
            'status' => ['required', 'in:not_paid,upcoming,due,paid,partial'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'paid_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $payout = $project->projectPayouts()->firstOrNew(['type' => $validated['type']]);
        $payout->status = $validated['status'];
        $payout->amount_paid = $validated['amount_paid'] ?? null;
        $payout->paid_at = $validated['paid_at'] ?? null;
        $payout->note = $validated['note'] ?? null;
        $payout->save();

        if (in_array($payout->type, [ProjectPayout::TYPE_DEVELOPER, ProjectPayout::TYPE_SALES], true)) {
            event(new PayoutStatusChanged($payout->fresh()));
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true]);
        }

        return redirect()
            ->back()
            ->with('success', ProjectPayout::typeLabel($validated['type']) . ' payout updated.');
    }
}
