<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('direction', 20); // erp_to_sheet | sheet_to_erp
            $table->string('entity', 50)->default('project');
            $table->unsignedBigInteger('erp_project_id')->nullable();
            $table->string('status', 20); // success | error
            $table->text('message')->nullable();
            $table->timestamps();
        });

        Schema::table('sync_logs', function (Blueprint $table) {
            $table->index(['direction', 'created_at']);
            $table->index('erp_project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
