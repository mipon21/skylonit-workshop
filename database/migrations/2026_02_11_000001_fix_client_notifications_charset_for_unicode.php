<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure client_notifications supports full Unicode (Bengali, emoji, etc.)
        \DB::statement('ALTER TABLE client_notifications CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    public function down(): void
    {
        // Reverting charset changes is rarely needed; leave as no-op
    }
};
