<?php

use App\Models\Invoice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Remove duplicate invoices: keep one invoice per payment_id (smallest id), delete the rest and their files.
     */
    public function up(): void
    {
        $paymentIdsWithDuplicates = Invoice::query()
            ->select('payment_id')
            ->groupBy('payment_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('payment_id');

        foreach ($paymentIdsWithDuplicates as $paymentId) {
            $invoices = Invoice::where('payment_id', $paymentId)->orderBy('id')->get();
            $keep = $invoices->first();
            foreach ($invoices->skip(1) as $duplicate) {
                if ($duplicate->file_path && Storage::exists($duplicate->file_path)) {
                    Storage::delete($duplicate->file_path);
                }
                $duplicate->delete();
            }
        }
    }

    public function down(): void
    {
        // Cannot restore deleted duplicate invoices
    }
};
