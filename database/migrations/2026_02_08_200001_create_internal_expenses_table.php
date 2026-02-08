<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->enum('primary_fund', ['overhead'])->default('overhead');
            $table->enum('fallback_fund', ['profit', 'investment'])->nullable();
            $table->enum('funded_from', ['overhead', 'profit', 'investment']);
            $table->foreignId('investment_id')->nullable()->constrained('investments')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_expenses');
    }
};
