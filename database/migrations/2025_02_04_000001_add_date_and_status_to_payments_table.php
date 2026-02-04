<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->date('payment_date')->nullable()->after('note');
            $table->string('status', 20)->default('completed')->after('payment_date'); // upcoming, due, completed
        });

        // Existing rows: treat as completed so total_paid stays correct
        DB::table('payments')->whereNull('payment_date')->update(['payment_date' => now()->toDateString()]);
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_date', 'status']);
        });
    }
};
