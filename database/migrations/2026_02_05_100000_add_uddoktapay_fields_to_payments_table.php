<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway', 50)->nullable()->after('status'); // 'uddoktapay', 'manual'
            $table->string('payment_status', 20)->default('DUE')->after('gateway'); // 'DUE', 'PAID'
            $table->string('gateway_invoice_id')->nullable()->after('payment_status');
            $table->text('payment_link')->nullable()->after('gateway_invoice_id');
            $table->timestamp('paid_at')->nullable()->after('payment_link');
            $table->string('paid_method', 20)->nullable()->after('paid_at'); // 'gateway', 'cash'
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'gateway',
                'payment_status',
                'gateway_invoice_id',
                'payment_link',
                'paid_at',
                'paid_method',
            ]);
        });
    }
};
