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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Basic information
            $table->string('name'); // "TD Bank Checking", "Cash CAD"
            $table->enum('type', [
                'cash',           // cash
                'checking',       // checking account
                'savings',        // savings account
                'credit_card',    // credit card
                'investment',     // investment account (TFSA, RRSP)
                'loan',           // loan
                'mortgage',       // mortgage
                'other'
            ]);

            // Balances
            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->string('currency', 3)->default('CAD');

            // Banking details
            $table->string('institution')->nullable(); // TD Bank, RBC, Tangerine
            $table->string('account_number')->nullable();
            $table->string('routing_number')->nullable();

            // For credit cards
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->integer('billing_day')->nullable(); // billing statement day
            $table->integer('payment_due_day')->nullable();

            // For loans
            $table->decimal('interest_rate', 5, 2)->nullable();
            $table->date('loan_start_date')->nullable();
            $table->date('loan_end_date')->nullable();

            // Visual styling
            $table->string('icon')->nullable();
            $table->string('color', 7)->default('#10B981');

            // Settings
            $table->boolean('include_in_total')->default(true); // include in total balance
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
