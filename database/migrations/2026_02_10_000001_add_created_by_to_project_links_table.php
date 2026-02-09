<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_links', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('project_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_links', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });
    }
};
