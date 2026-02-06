<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('exclude_from_overhead_profit');
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('is_public')->default(true)->after('due_date');
        });
        Schema::table('bugs', function (Blueprint $table) {
            $table->boolean('is_public')->default(true)->after('attachment_path');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
        Schema::table('bugs', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }
};
