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
        Schema::create('recurring_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Base transaction information
            $table->string('name'); // "Apartment rent", "Salary"
            $table->enum('type', ['income', 'expense', 'transfer']);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('CAD');

            // Categorization
            $table->foreignId('category_id')->constrained();
            $table->foreignId('account_id')->constrained();
            $table->foreignId('to_account_id')->nullable()->constrained('accounts')->nullOnDelete(); // for transfers

            $table->string('description')->nullable();
            $table->string('merchant')->nullable();

            // Schedule
            $table->enum('frequency', [
            'daily',
            'weekly',
            'biweekly',      // every 2 weeks
            'monthly',
            'bimonthly',     // every 2 months
            'quarterly',
            'semi_annually', // twice a year
            'annually'
            ]);

            $table->integer('frequency_value')->default(1); // multiplier (e.g., every 3 months)
            $table->integer('day_of_month')->nullable(); // day of month for monthly
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])->nullable();

            // Activity period
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null = infinite
            $table->date('next_occurrence'); // date of next creation
            $table->date('last_occurrence')->nullable();

            // Automation
            $table->boolean('auto_create')->default(false); // create automatically
            $table->integer('create_days_before')->default(0); // how many days before to create

            // Status
            $table->boolean('is_active')->default(true);
            $table->integer('total_occurrences')->default(0); // how many times already created

            $table->timestamps();

            $table->index(['user_id', 'next_occurrence', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_templates');
    }
};
