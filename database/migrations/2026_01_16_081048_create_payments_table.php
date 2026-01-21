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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id'); // Foreign key to properties table
            $table->unsignedBigInteger('tenancy_id')->nullable(); // Foreign key to tenancies table
            $table->unsignedBigInteger('payer_user_id')->nullable(); // Foreign key to users table
            $table->enum('source', ['mpesa', 'manual'])->default('manual');
            $table->decimal('amount', 10, 2); // Payment amount
            $table->timestamp('paid_at'); // When payment was made
            $table->string('reference')->nullable(); // Reference for the payment
            $table->enum('status', ['pending', 'confirmed', 'refunded', 'cancelled'])->default('confirmed');
            $table->timestamps();

            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('tenancy_id')->references('id')->on('tenancies')->onDelete('set null');
            $table->foreign('payer_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
