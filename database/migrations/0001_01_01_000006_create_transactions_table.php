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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Main fields
            $table->enum('type', ['income', 'expense', 'transfer']); // transaction type
            $table->decimal('amount', 15, 2); // amount (always positive)
            $table->string('currency', 3)->default('CAD'); // currency (ISO 4217)
            $table->date('transaction_date'); // transaction date

             // Categorization
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->constrained()->onDelete('cascade'); // account (card, cash, etc.)

            // Additional information
            $table->string('description')->nullable(); // description
            $table->text('notes')->nullable(); // detailed notes
            $table->string('merchant')->nullable(); // merchant/recipient
            $table->string('reference')->nullable(); // receipt number, invoice, etc.

            // For transfers between accounts
            $table->foreignId('related_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->foreignId('to_account_id')->nullable()->constrained('accounts')->nullOnDelete();

            // For recurring transactions
            $table->foreignId('recurring_template_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_recurring')->default(false);

            // Status and metadata
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->json('metadata')->nullable(); // flexible field for additional data
            $table->json('tags')->nullable(); // tags for flexible categorization

            $table->timestamps();
            $table->softDeletes(); // for the ability to restore deleted records

            // Indexes for fast search
            $table->index(['user_id', 'transaction_date']);
            $table->index(['user_id', 'type']);
            $table->index(['category_id']);
            $table->index(['account_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
