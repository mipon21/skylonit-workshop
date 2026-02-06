<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_public');
            $table->string('short_description', 500)->nullable()->after('is_featured');
            $table->string('featured_image_path', 500)->nullable()->after('short_description');
            $table->string('tech_stack', 255)->nullable()->after('featured_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'short_description', 'featured_image_path', 'tech_stack']);
        });
    }
};
