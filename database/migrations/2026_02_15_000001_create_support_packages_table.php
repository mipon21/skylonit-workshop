<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('package_duration', 2); // '1','3','6','12'
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedTinyInteger('months_count');
            $table->string('package_label');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_status', ['due', 'paid'])->default('due');
            $table->text('payment_link')->nullable();
            $table->string('gateway_invoice_id')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('invoice_path')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'payment_status']);
            $table->index(['client_id', 'payment_status']);
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_packages');
    }
};
