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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete(); // target account

            $table->string('name'); // "Europe Vacation", "Emergency Fund"
            $table->text('description')->nullable();

            // Target amount
            $table->decimal('target_amount', 15, 2);
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('CAD');

            // Deadline
            $table->date('target_date')->nullable();
            $table->date('achieved_date')->nullable();

            // Recommendations
            $table->decimal('recommended_monthly_saving', 15, 2)->nullable(); // auto-calculated

            // Visual customization
            $table->string('icon')->nullable();
            $table->string('color', 7)->default('#8B5CF6');

            // Status
            $table->enum('status', ['active', 'achieved', 'paused', 'abandoned'])->default('active');
            $table->integer('progress_percentage')->default(0); // 0-100

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
