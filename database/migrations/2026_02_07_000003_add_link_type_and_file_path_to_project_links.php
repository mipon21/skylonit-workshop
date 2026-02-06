<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_links', function (Blueprint $table) {
            $table->string('link_type', 20)->default('url')->after('project_id'); // 'url' | 'apk'
            $table->string('file_path')->nullable()->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('project_links', function (Blueprint $table) {
            $table->dropColumn(['link_type', 'file_path']);
        });
    }
};
