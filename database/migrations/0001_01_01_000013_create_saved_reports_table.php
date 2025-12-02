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
        Schema::create('saved_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('name'); // "Q4 2025 Expenses by Category"
            $table->enum('type', [
                'expense_by_category',
                'income_vs_expense',
                'cash_flow',
                'budget_performance',
                'net_worth',
                'custom'
            ]);

            $table->json('filters'); // report parameters (dates, categories, accounts)
            $table->json('settings'); // display settings

            $table->boolean('is_favorite')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_reports');
    }
};
