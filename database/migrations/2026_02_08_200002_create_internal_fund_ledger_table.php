<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_fund_ledger', function (Blueprint $table) {
            $table->id();
            $table->enum('fund_type', ['overhead', 'profit', 'investment']);
            $table->enum('reference_type', ['internal_expense', 'manual_adjustment']);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('investment_id')->nullable()->constrained('investments')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('direction', ['debit', 'credit']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_fund_ledger');
    }
};
