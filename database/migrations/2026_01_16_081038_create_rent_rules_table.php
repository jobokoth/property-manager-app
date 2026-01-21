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
        Schema::create('rent_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id'); // Foreign key to properties table
            $table->unsignedBigInteger('unit_id')->nullable(); // Optional: specific to a unit
            $table->tinyInteger('due_day')->default(5); // Day of month rent is due
            $table->tinyInteger('grace_days')->default(3); // Grace period after due date
            $table->enum('late_fee_mode', ['fixed', 'percent'])->default('fixed');
            $table->decimal('late_fee_value', 8, 2)->default(0); // Amount or percentage for late fee
            $table->timestamps();

            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_rules');
    }
};
