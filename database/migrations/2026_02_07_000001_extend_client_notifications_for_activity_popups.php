<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_notifications', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
            $table->unsignedBigInteger('activity_id')->nullable()->after('project_id');
            $table->text('message')->nullable()->after('title');
            $table->boolean('is_read')->default(false)->after('message');
            $table->unsignedBigInteger('payment_id')->nullable()->after('is_read');
            $table->unsignedBigInteger('invoice_id')->nullable()->after('payment_id');
        });

        if (Schema::hasColumn('client_notifications', 'body')) {
            DB::table('client_notifications')->update([
                'message' => DB::raw('body'),
                'is_read' => DB::raw('CASE WHEN read_at IS NOT NULL THEN 1 ELSE 0 END'),
            ]);
            Schema::table('client_notifications', function (Blueprint $table) {
                $table->dropColumn('body');
            });
        }
        if (Schema::hasColumn('client_notifications', 'read_at')) {
            Schema::table('client_notifications', function (Blueprint $table) {
                $table->dropColumn('read_at');
            });
        }

    }

    public function down(): void
    {
        Schema::table('client_notifications', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn(['activity_id', 'message', 'is_read', 'payment_id', 'invoice_id']);
        });
        Schema::table('client_notifications', function (Blueprint $table) {
            $table->text('body')->nullable();
            $table->timestamp('read_at')->nullable();
        });
    }
};
