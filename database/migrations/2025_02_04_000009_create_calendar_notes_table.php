<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('note_date');
            $table->text('body');
            $table->timestamps();

            $table->unique(['user_id', 'note_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_notes');
    }
};
