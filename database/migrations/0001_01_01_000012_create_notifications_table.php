<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->enum('type', [
                'budget_alert',      // budget exceeded
                'goal_milestone',    // goal milestone reached
                'bill_reminder',     // bill payment reminder
                'low_balance',       // low balance
                'recurring_created', // recurring transaction created
                'other'
            ]);

            $table->string('title');
            $table->text('message');

            $table->morphs('notifiable'); // relation to object (budget, goal, etc.)

            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            $table->json('metadata')->nullable(); // additional data

            $table->timestamps();

            $table->index(['user_id', 'is_read', 'created_at']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
