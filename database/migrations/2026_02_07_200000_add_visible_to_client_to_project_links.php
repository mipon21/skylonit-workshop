<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_links', function (Blueprint $table) {
            $table->boolean('visible_to_client')->default(true)->after('is_public');
        });
        // Existing links: keep same behavior — "public" (is_public=1) was visible to client+guest, so visible_to_client=1; "private" (is_public=0) was admin-only, so visible_to_client=0
        DB::table('project_links')->where('is_public', false)->update(['visible_to_client' => false]);
    }

    public function down(): void
    {
        Schema::table('project_links', function (Blueprint $table) {
            $table->dropColumn('visible_to_client');
        });
    }
};
