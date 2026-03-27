<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->boolean('is_valid')->default(true)->after('is_public');
            $table->text('invalid_note')->nullable()->after('is_valid');
            $table->string('invalid_attachment_path')->nullable()->after('invalid_note');
        });
    }

    public function down(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->dropColumn(['is_valid', 'invalid_note', 'invalid_attachment_path']);
        });
    }
};
