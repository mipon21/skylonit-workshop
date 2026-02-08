<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->string('investor_name');
            $table->decimal('amount', 15, 2);
            $table->date('invested_at');
            $table->enum('risk_level', ['low', 'medium', 'high']);
            $table->decimal('profit_share_percent', 5, 2);
            $table->decimal('return_cap_multiplier', 8, 2);
            $table->decimal('return_cap_amount', 15, 2);
            $table->decimal('returned_amount', 15, 2)->default(0);
            $table->enum('status', ['active', 'exited'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
