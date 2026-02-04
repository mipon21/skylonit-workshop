<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('project_name');
            $table->string('project_code')->nullable();
            $table->double('contract_amount', 15, 2)->default(0);
            $table->date('contract_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('status')->default('Pending'); // Pending / Running / Complete
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
