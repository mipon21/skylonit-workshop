<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profit_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_id')->constrained('investments')->cascadeOnDelete();
            $table->string('period', 7); // e.g. 2026-02 (month/year)
            $table->decimal('profit_pool_amount', 15, 2);
            $table->decimal('investor_share_amount', 15, 2);
            $table->decimal('founder_share_amount', 15, 2);
            $table->timestamps();

            $table->unique(['investor_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profit_distributions');
    }
};
