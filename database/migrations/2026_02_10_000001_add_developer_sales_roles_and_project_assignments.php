<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Allow 'developer' and 'sales' roles (MySQL: extend enum)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','client','developer','sales') NOT NULL DEFAULT 'admin'");

        Schema::create('project_developers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['project_id', 'user_id']);
        });

        Schema::create('project_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['project_id', 'user_id']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('assigned_to_user_id')->nullable()->after('project_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('bugs', function (Blueprint $table) {
            $table->foreignId('assigned_to_user_id')->nullable()->after('project_id')->constrained('users')->nullOnDelete();
        });

        Schema::create('user_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // bank, mobile_banking, etc.
            $table->string('label')->nullable();
            $table->text('details')->nullable(); // JSON or plain text (account number, bank name, etc.)
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('user_payment_methods');
        Schema::table('bugs', function (Blueprint $table) {
            $table->dropForeign(['assigned_to_user_id']);
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_to_user_id']);
        });
        Schema::dropIfExists('project_sales');
        Schema::dropIfExists('project_developers');
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','client') NOT NULL DEFAULT 'admin'");
    }
};
