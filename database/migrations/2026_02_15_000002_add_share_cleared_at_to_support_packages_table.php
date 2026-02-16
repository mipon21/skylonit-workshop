<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_packages', function (Blueprint $table) {
            $table->timestamp('share_cleared_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('support_packages', function (Blueprint $table) {
            $table->dropColumn('share_cleared_at');
        });
    }
};
