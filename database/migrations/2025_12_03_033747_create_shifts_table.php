<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_id');  // ← Вміст Telegram ID, без FK
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->dateTime('break_start')->nullable();
            $table->dateTime('break_end')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamps();

            $table->index('telegram_id');
            $table->index('start_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
