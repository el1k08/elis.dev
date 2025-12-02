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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();

            $table->string('from_currency', 3); // USD
            $table->string('to_currency', 3);   // CAD
            $table->decimal('rate', 15, 6);      // exchange rate

            $table->date('date'); // rate date
            $table->string('source')->default('manual'); // api, manual, ecb

            $table->timestamps();

            $table->unique(['from_currency', 'to_currency', 'date']);
            $table->index(['date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
