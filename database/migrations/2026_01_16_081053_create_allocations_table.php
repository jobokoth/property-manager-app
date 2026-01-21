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
        Schema::create('allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id'); // Foreign key to payments table
            $table->enum('allocation_type', ['rent', 'water', 'other']); // Type of allocation
            $table->decimal('amount', 10, 2); // Allocated amount
            $table->string('period_month'); // Month in YYYY-MM format
            $table->text('notes')->nullable(); // Additional notes about the allocation
            $table->timestamps();

            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocations');
    }
};
