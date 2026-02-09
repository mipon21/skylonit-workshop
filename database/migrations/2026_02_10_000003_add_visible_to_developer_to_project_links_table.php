<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_links', function (Blueprint $table) {
            $table->boolean('visible_to_developer')->default(false)->after('visible_to_client');
        });
    }

    public function down(): void
    {
        Schema::table('project_links', function (Blueprint $table) {
            $table->dropColumn('visible_to_developer');
        });
    }
};
