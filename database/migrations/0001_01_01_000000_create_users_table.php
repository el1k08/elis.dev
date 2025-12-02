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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('workos_id')->unique();
            $table->rememberToken();
            $table->text('avatar');
            $table->timestamps();

            // Profile settings
            $table->string('default_currency', 3)->default('CAD');
            $table->string('timezone')->default('America/St_Johns');
            $table->string('locale')->default('en');
            $table->enum('date_format', ['Y-m-d', 'd/m/Y', 'm/d/Y'])->default('Y-m-d');

            // Financial settings
            $table->date('financial_year_start')->nullable(); // financial year start date
            $table->decimal('monthly_budget', 15, 2)->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
