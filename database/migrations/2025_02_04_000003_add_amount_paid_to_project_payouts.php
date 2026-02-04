<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_payouts', function (Blueprint $table) {
            if (! Schema::hasColumn('project_payouts', 'amount_paid')) {
                $table->double('amount_paid', 15, 2)->nullable()->after('status');
            }
            if (! Schema::hasColumn('project_payouts', 'paid_at')) {
                $table->date('paid_at')->nullable()->after('amount_paid');
            }
            if (! Schema::hasColumn('project_payouts', 'note')) {
                $table->text('note')->nullable()->after('paid_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_payouts', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'paid_at', 'note']);
        });
    }
};
