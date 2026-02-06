<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make all existing projects public and set default to true for new projects
        DB::table('projects')->update(['is_public' => true]);
        DB::statement('ALTER TABLE projects MODIFY is_public TINYINT(1) NOT NULL DEFAULT 1');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE projects MODIFY is_public TINYINT(1) NOT NULL DEFAULT 0');
    }
};
