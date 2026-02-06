<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Option B - Meter-based water billing.
     */
    public function up(): void
    {
        Schema::create('water_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenancy_id')->constrained()->cascadeOnDelete();
            $table->string('period_month', 7); // YYYY-MM format
            $table->decimal('units_consumed', 10, 2);
            $table->decimal('rate_per_unit', 10, 2);
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'partial'])->default('pending');
            $table->timestamps();

            $table->unique(['tenancy_id', 'period_month']);
            $table->index('period_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_bills');
    }
};
