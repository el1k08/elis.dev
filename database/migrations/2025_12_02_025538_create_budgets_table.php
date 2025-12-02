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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name'); // "Groceries December 2025"
            $table->decimal('amount', 15, 2); // budget limit
            $table->string('currency', 3)->default('CAD');

            // Period
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'custom']);
            $table->date('start_date');
            $table->date('end_date');

            // Progress
            $table->decimal('spent_amount', 15, 2)->default(0); // auto-updated
            $table->decimal('remaining_amount', 15, 2)->default(0);

            // Alerts
            $table->boolean('alert_enabled')->default(true);
            $table->integer('alert_threshold')->default(80); // % usage for alert
            $table->boolean('alert_sent')->default(false);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'start_date', 'end_date']);
            $table->index(['category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
