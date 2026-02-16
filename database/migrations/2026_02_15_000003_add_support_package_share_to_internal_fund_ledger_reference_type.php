<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE internal_fund_ledger MODIFY COLUMN reference_type ENUM('internal_expense', 'manual_adjustment', 'support_package_share') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE internal_fund_ledger MODIFY COLUMN reference_type ENUM('internal_expense', 'manual_adjustment') NOT NULL");
    }
};
