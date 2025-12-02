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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // null = system category
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Visual styling
            $table->string('icon')->nullable(); // emoji or icon name
            $table->string('color', 7)->default('#3B82F6'); // HEX color

            // Type and hierarchy
            $table->enum('type', ['income', 'expense', 'both'])->default('expense');
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->integer('sort_order')->default(0);

            // Budgeting
            $table->decimal('monthly_budget', 15, 2)->nullable();
            $table->boolean('budget_alert')->default(false); // alert when exceeded

            // Status
            $table->boolean('is_system')->default(false); // system category (cannot be deleted)
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
