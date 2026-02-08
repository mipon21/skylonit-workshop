<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Distribution settings: applied only at project creation (and when explicitly edited).
     * Backward compatibility: existing projects keep current values; no auto-migration.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('developer_sales_mode')->default(false)->after('exclude_from_overhead_profit');
            $table->boolean('sales_commission_enabled')->default(true)->after('developer_sales_mode');
            $table->decimal('sales_percentage', 5, 2)->default(25)->after('sales_commission_enabled');
            $table->decimal('developer_percentage', 5, 2)->default(40)->after('sales_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'developer_sales_mode',
                'sales_commission_enabled',
                'sales_percentage',
                'developer_percentage',
            ]);
        });
    }
};
